<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Catalog\Model\Product\Action;
use Magento\Catalog\Model\Product as ProductMainModel;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class ProductsObserver
{

    private $indexer;
    private $productsManager;
    private $configurable;

   /**
    * @param IndexerManager $indexerRegistry
    */
    public function __construct(
        IndexerManager $indexerManager,
        Configurable $configurable,
        ProductsManager $productsManager
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->configurable = $configurable;
        $this->productsManager = $productsManager;
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

    private function getProductIdsToIndex($productIds)
    {
        $productIdsToIndex = $productIds;
        foreach ($productIds as $productId) {
            $parentProductIds = $this->configurable->getParentIdsByChild($productId);
            if (is_array($parentProductIds) && count($parentProductIds)) {
                array_push($productIdsToIndex, ...$parentProductIds);
            }
        }

        return array_values(array_unique($productIdsToIndex));
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
    public function afterUpdateAttributes(Action $subject, Action $result = null, $productIds)
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
    public function afterUpdateWebsites(Action $subject, Action $result = null, array $productIds)
    {
        if (!$this->indexer->isScheduled()) {
            $productIds = $this->getProductIdsToIndex($productIds);
            $this->indexer->reindexList($productIds);
        }

        return $result;
    }
}
