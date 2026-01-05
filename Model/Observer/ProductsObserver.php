<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product as ProductMainModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\Model\DeletedProducts;

class ProductsObserver
{

    private $indexer;
    private $productsManager;
    private $configurable;
    private $deletedProducts;

   /**
    * @param IndexerManager $indexerRegistry
    */
    public function __construct(
        IndexerManager $indexerManager,
        Configurable $configurable,
        ProductsManager $productsManager,
        DeletedProducts $deletedProducts
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->configurable = $configurable;
        $this->productsManager = $productsManager;
        $this->deletedProducts = $deletedProducts;
    }

   /**
    * @param ProductResourceModel $product
    * @param ProductResourceModel $result
    * @param ProductMainModel $product
    * @return ProductMainModel[]
    */
    public function afterSave(
        ProductResourceModel $productResourceModel,
        ProductResourceModel $resourceModelResult,
        ProductMainModel $product
    ) {
        $productResourceModel->addCommitCallback(function () use ($product) {
            if (!$this->indexer->isScheduled()) {
                $productIds = $this->getProductIdsToIndex([$product->getId()]);
                $this->indexer->reindexList($productIds);
            }
        });

        return $resourceModelResult;
    }

    public function getProductIdsToIndex(array $productIds): array
    {
        $ids = $productIds;
        $additionalIds = [];

        foreach ($productIds as $productId) {

            $parentProductIds = $this->configurable->getParentIdsByChild($productId);
            if (!empty($parentProductIds)) {
                foreach ($parentProductIds as $id) {
                    $additionalIds[] = $id;
                }
            }

            $childProductIds = $this->configurable->getChildrenIds($productId);
            if (!empty($childProductIds)) {
                foreach ($childProductIds as $childIds) {
                    foreach ($childIds as $id) {
                        $additionalIds[] = $id;
                    }
                }
            }
        }

        return array_values(array_unique(array_merge($ids, $additionalIds)));
    }

   /**
    * @param ProductResourceModel $productResource
    * @param ProductResourceModel $result
    * @param ProductMainModel $product
    * @return ProductMainModel[]
    */
    public function afterDelete(
        ProductResourceModel $productResource,
        ProductResourceModel $resourceModelResult,
        ProductMainModel $product
    ) {
        $productResource->addCommitCallback(function () use ($product) {
            if (!$this->indexer->isScheduled()) {
                  $productIds = $this->getProductIdsToIndex([$product->getId()]);
                  $this->deletedProducts->addDeletedProducts($productIds);
                  $this->indexer->reindexList($productIds);
            }
        });

        return $resourceModelResult;
    }

   /**
    * @param Action $subject
    * @param Action|null $result
    * @param array $productIds
    *
    * @return Action
    */
    public function afterUpdateAttributes(Action $subject, ?Action $result, array $productIds)
    {
        if (!$this->indexer->isScheduled()) {
            $productIds = $this->getProductIdsToIndex($productIds);
            $this->indexer->reindexList($productIds);
        }

        return $result;
    }

   /**
    * @param Action $subject
    * @param Action|null $result
    * @param array $productIds
    *
    * @return mixed
    */
    public function afterUpdateWebsites(Action $subject, ?Action $result, array $productIds)
    {
        if (!$this->indexer->isScheduled()) {
            $productIds = $this->getProductIdsToIndex($productIds);
            $this->indexer->reindexList($productIds);
        }

        return $result;
    }
}
