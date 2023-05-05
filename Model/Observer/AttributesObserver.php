<?php

namespace Wizzy\Search\Model\Observer;

use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\WizzyProduct;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Magento\Catalog\Model\ResourceModel\Attribute\Interceptor as AttributeInterceptor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor as EavAttributeInterceptor;

class AttributesObserver
{

    private $indexer;
    private $wizzyProduct;
    private $productsManager;
    private $storeAdvancedConfig;

    /**
     * @param IndexerManager $indexerManager
     */
    public function __construct(
        IndexerManager $indexerManager,
        WizzyProduct $wizzyProduct,
        ProductsManager $productsManager,
        StoreAdvancedConfig $storeAdvancedConfig
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->wizzyProduct = $wizzyProduct;
        $this->productsManager = $productsManager;
        $this->storeAdvancedConfig = $storeAdvancedConfig;
    }

    public function afterSave(
        AttributeInterceptor $attributeInterceptor,
        AttributeInterceptor $attributeInterceptorResult,
        EavAttributeInterceptor $attribute
    ) {
        $productIdsToUpdate = $this->getEffectedProductIds($attribute);
        $this->addProductsInSync($productIdsToUpdate);
        return $attributeInterceptorResult;
    }

    public function beforeDelete(
        AttributeInterceptor $attributeInterceptor,
        EavAttributeInterceptor $attribute
    ) {
        $productIdsToUpdate = $this->getEffectedProductIds($attribute);
        $this->addProductsInSync($productIdsToUpdate);
    }

    private function getEffectedProductIds(EavAttributeInterceptor $attribute)
    {
        $productIds = [];
        if ($this->storeAdvancedConfig->hasToAddProductsInSyncOnAttributeSave() === true) {
            $products = $this->productsManager->getProductsByAttribute(
                $attribute->getAttributeCode(),
                $attribute->getStoreId()
            );
            foreach ($products as $product) {
                $productIds[] = $product->getId();
            }
        }

        return $productIds;
    }

    private function addProductsInSync(array $productIds)
    {
        if (count($productIds) == 0) {
            return;
        }
        if (!$this->indexer->isScheduled()) {
            $productIds = array_chunk($productIds, 4000);
            foreach ($productIds as $productIdsChunk) {
                $this->indexer->reindexList($productIdsChunk);
            }
        } else {
            $this->wizzyProduct->addProductsInChangeLog($productIds);
        }
    }
}
