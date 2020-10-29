<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\API\Wizzy\Modules\Products;
use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Catalogue\Mappers\ProductsMapper;
use Wizzy\Search\Services\Catalogue\OrderItemManager;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Catalogue\ReviewRatingsManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\SessionStorage\CategoriesSessionStorage;
use Wizzy\Search\Services\Queue\SessionStorage\ProductsSessionStorage;
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
    private $categoriesManager;
    private $categoriesSessionStorage;
    private $productsSessionStorage;

    public function __construct(
        ProductsManager $productsManager,
        OrderItemManager $orderItemManager,
        StoreGeneralConfig $storeGeneralConfig,
        EntitiesSync $entitiesSync,
        Products $productsConnector,
        ProductsMapper $productsMapper,
        CategoriesManager $categoriesManager,
        ReviewRatingsManager $reviewRatingsManager,
        CategoriesSessionStorage $categoriesSessionStorage,
        ProductsSessionStorage $productsSessionStorage
    ) {
        $this->productsManager = $productsManager;
        $this->productsMapper = $productsMapper;
        $this->reviewRatingsManager = $reviewRatingsManager;
        $this->productsConnector = $productsConnector;
        $this->entitiesSync = $entitiesSync;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->orderItemManager = $orderItemManager;
        $this->categoriesManager = $categoriesManager;
        $this->productsSessionStorage = $productsSessionStorage;
        $this->categoriesSessionStorage = $categoriesSessionStorage;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);
        if (!$this->storeGeneralConfig->isSyncEnabled() || !isset($data['products'])) {
            return true;
        }

        $productIds = $data['products'];

        $products = $this->productsManager->getProductsByIds($productIds, $storeId);
        $this->setSessionData($products, $productIds, $storeId);

        $productIdsToDelete = $this->findDeletedAndInactiveProducts($productIds, $products);
        $productIdsForReview = array_merge($productIds, $this->getChildProductIds($products));
        $productReviews = $this->reviewRatingsManager->getSummary($productIdsForReview, $storeId);
        $orderItems = $this->orderItemManager->getSummary($productIdsForReview, $storeId);

        $products = $this->productsMapper->mapAll($products, $productReviews, $orderItems, $storeId);
        $saveResponse = $this->submitSaveProductsRequest($products, $storeId);

        if ($saveResponse === true) {
            $this->submitDeleteProductsRequest($productIdsToDelete, $storeId);
            $this->entitiesSync->markEntitiesAsSynced($productIds, $storeId, EntitiesSync::ENTITY_TYPE_PRODUCT);
            return true;
        }

        return $saveResponse;
    }

    /**
     * Set session data for queue processing.
     *
     * @param $products
     * @param $productIds
     * @param $storeId
     */
    private function setSessionData($products, $productIds, $storeId)
    {
        $productObjectByIds = $this->getProductObjectByIds($products, $productIds, $storeId);
        $productCategories = $this->getProductCategories($productObjectByIds, $storeId);

        $this->productsSessionStorage->set($productObjectByIds);
        $this->categoriesSessionStorage->set($productCategories);
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

    /**
     * Get all product objects including child and parent by IDs.
     *
     * @param $products
     * @param $productIds
     * @param $storeId
     * @return array
     */
    public function getProductObjectByIds($products, $productIds, $storeId)
    {
        $parentProductIds = $this->productsManager->getParentProductIds($productIds);
        $childProductIds = $this->getChildProductIds($products);
        $productIdsToQuery = array_merge($parentProductIds, $childProductIds);

        $productsToMerge = $this->productsManager->getProductsByIds($productIdsToQuery, $storeId);
        $productObjectByIds = [];

        foreach ($products as $product) {
            $productObjectByIds[$product->getId()] = $product;
        }

        foreach ($productsToMerge as $productToMerge) {
            $productObjectByIds[$productToMerge->getId()] = $productToMerge;
        }

        return $productObjectByIds;
    }

    /**
     * Get product categories object by Ids for queue processing.
     *
     * @param $products
     * @param $storeId
     * @return array
     */
    private function getProductCategories($products, $storeId)
    {
        $categoryIdsSet = [];

        foreach ($products as $product) {
            $categoryIds = $product->getCategoryIds();

            foreach ($categoryIds as $categoryId) {
                $categoryIdsSet[$categoryId] = true;
            }
        }

        $productCategories = $this->categoriesManager->fetchByIds(array_keys($categoryIdsSet), $storeId);
        $productCategoriesByIds = [];
        $otherCategoryIds = [];

        foreach ($productCategories as $productCategory) {
            $productCategoriesByIds[$productCategory->getId()] = $productCategory;

            $pathIds = $productCategory->getPathIds();
            unset($pathIds[0]);
            $pathIds = array_values($pathIds);

            foreach ($pathIds as $pathId) {
                $otherCategoryIds[$pathId] = true;
            }

            $parentCategoryIds = $this->categoriesManager->getParentIdsOfCategory($productCategory);

            foreach ($parentCategoryIds as $parentCategoryId) {
                $otherCategoryIds[$parentCategoryId] = true;
            }
        }

        $productCategories = $this->categoriesManager->fetchByIds(array_keys($otherCategoryIds), $storeId);
        foreach ($productCategories as $productCategory) {
            $productCategoriesByIds[$productCategory->getId()] = $productCategory;
        }

        return $productCategoriesByIds;
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
