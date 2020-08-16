<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\API\Wizzy\Modules\Products;
use Wizzy\Search\Services\Catalogue\Mappers\ProductsMapper;
use Wizzy\Search\Services\Catalogue\OrderItemManager;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Catalogue\ReviewRatingsManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Store\StoreGeneralConfig;

class IndexProductsProcessor extends QueueProcessorBase
{

    private $productsManager;
    private $productsMapper;
    private $reviewRatingsManager;
    private $productsConnector;
    private $entitiesSync;
    private $storeGeneralConfig;
    private $orderItemManager;

    public function __construct(
        ProductsManager $productsManager,
        OrderItemManager $orderItemManager,
        StoreGeneralConfig $storeGeneralConfig,
        EntitiesSync $entitiesSync,
        Products $productsConnector,
        ProductsMapper $productsMapper,
        ReviewRatingsManager $reviewRatingsManager
    ) {
        $this->productsManager = $productsManager;
        $this->productsMapper = $productsMapper;
        $this->reviewRatingsManager = $reviewRatingsManager;
        $this->productsConnector = $productsConnector;
        $this->entitiesSync = $entitiesSync;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->orderItemManager = $orderItemManager;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);
        if (!$this->storeGeneralConfig->isSyncEnabled() || !isset($data['products'])) {
            return true;
        }

        $productIds = $data['products'];

        $products = $this->productsManager->getProductsByIds($productIds, $storeId);
        $productIdsToDelete = $this->findDeletedAndInactiveProducts($productIds, $products);
        $productIdsForReview = array_merge($productIds, $this->getChildProductIds($products));
        $productReviews = $this->reviewRatingsManager->getSummary($productIdsForReview, $storeId);
        $orderItems = $this->orderItemManager->getSummary($productIdsForReview, $storeId);

        $products = $this->productsMapper->mapAll($products, $productReviews, $orderItems, $storeId);
        $saveResponse = $this->submitSaveProductsRequest($products, $storeId);

        if ($saveResponse) {
            $this->submitDeleteProductsRequest($productIdsToDelete, $storeId);
            $this->entitiesSync->markEntitiesAsSynced($productIds, $storeId, 'product');
            return true;
        }

        return false;
    }

   /**
    * Get child product IDs of given list of products.
    *
    * @param $products
    * @return array
    */
    private function getChildProductIds($products)
    {
        $childProductIds = [];
        foreach ($products as $product) {
            if ($product->getTypeID() == Configurable::TYPE_CODE) {
                $children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($children as $child) {
                    $childProductIds[] = $child->getId();
                }
            }
        }

        return $childProductIds;
    }

    private function submitSaveProductsRequest($products, $storeId)
    {
        return $this->productsConnector->save($products, $storeId);
    }

    private function submitDeleteProductsRequest($products, $storeId)
    {
        if (count($products) == 0) {
            return true;
        }
        return $this->productsConnector->delete($products, $storeId);
    }

    private function findDeletedAndInactiveProducts($productIds, $products)
    {
        $retrievedProductIds = [];

        foreach ($products as $product) {
            if (!$product->isDisabled() &&
                $product->getVisibility() != ((string)Visibility::VISIBILITY_IN_CATALOG)
            ) {
                $retrievedProductIds[] = $product->getId();
            }
        }

        return array_values(array_diff($productIds, $retrievedProductIds));
    }
}
