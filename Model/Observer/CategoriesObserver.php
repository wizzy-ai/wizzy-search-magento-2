<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Catalog\Model\Category as CategoryMainModel;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;
use Magento\Catalog\Model\Category\Interceptor as CategoryInterceptor;
use Magento\Catalog\Model\ResourceModel\Category\Interceptor as ResourceCategoryInterceptor;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\WizzyProduct;
use Wizzy\Search\Services\Queue\Processors\IndexCategoryProductsProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;

class CategoriesObserver
{

    private $queueManager;
    private $wizzyProduct;
    private $storeManager;

    /**
     * @param IndexerManager $indexerRegistry
     */
    public function __construct(WizzyProduct $wizzyProduct, QueueManager $queueManager, StoreManager $storeManager)
    {
        $this->queueManager = $queueManager;
        $this->wizzyProduct = $wizzyProduct;
        $this->storeManager = $storeManager;
    }

    /**
     * @param CategoryResourceModel $categoryResource
     * @param CategoryResourceModel $result
     * @param CategoryMainModel $category
     *
     * @return CategoryResourceModel
     */
    public function afterSave(
        CategoryResourceModel $categoryResourceModel,
        CategoryResourceModel $resourceModelResult,
        CategoryMainModel $category
    ) {
        $storeId = $this->storeManager->getCurrentStoreId();
        $jobData = $this->queueManager->getLatestInQueueByClass(IndexCategoryProductsProcessor::class, $storeId);
        $data = [
            'categoryIds' => [
                $category->getId()
            ]
        ];

        $existingCategoryIds = [];
        if ($jobData != null) {
            $existingCategoryIds = json_decode($jobData['data'], true);
            $existingCategoryIds = $existingCategoryIds['categoryIds'];

            if (count($existingCategoryIds) > 500) {
                $existingCategoryIds = [];
            }
        }

        if (count($existingCategoryIds)) {
            $categoryIds = array_unique(array_merge($existingCategoryIds, [$category->getId()]));
            $data['categoryIds'] = $categoryIds;
            $jobData['data'] = json_encode($data);
            $this->queueManager->edit($jobData);
        } else {
            $this->queueManager->enqueue(IndexCategoryProductsProcessor::class, $storeId, $data);
        }
        return $resourceModelResult;
    }

    /**
     * @param ResourceCategoryInterceptor $categoryResource
     * @param CategoryInterceptor $result
     *
     * @return ResourceCategoryInterceptor
     */
    public function beforeDelete(
        ResourceCategoryInterceptor $categoryResourceModel,
        CategoryInterceptor $categoryInterceptor
    ) {
        $productIdsToUpdate = array_keys($categoryInterceptor->getProductsPosition());
        $this->wizzyProduct->addProductsInSync($productIdsToUpdate);
    }
}
