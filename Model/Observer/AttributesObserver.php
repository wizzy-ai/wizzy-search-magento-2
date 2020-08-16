<?php

namespace Wizzy\Search\Model\Observer;

use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\WizzyProduct;

use Magento\Catalog\Model\ResourceModel\Attribute\Interceptor as AttributeInterceptor;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute\Interceptor as EavAttributeInterceptor;

class AttributesObserver
{

    private $indexer;
    private $wizzyProduct;
    private $productsManager;

  /**
   * @param IndexerManager $indexerManager
   */
    public function __construct(
        IndexerManager $indexerManager,
        WizzyProduct $wizzyProduct,
        ProductsManager $productsManager
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->wizzyProduct = $wizzyProduct;
        $this->productsManager = $productsManager;
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
        $products = $this->productsManager->getProductsByAttribute(
            $attribute->getAttributeCode(),
            $attribute->getStoreId()
        );

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    private function addProductsInSync(array $productIds)
    {
        if (!$this->indexer->isScheduled()) {
            $this->indexer->reindexList($productIds);
        } else {
            $this->wizzyProduct->addProductsInChangeLog($productIds);
        }
    }
}
