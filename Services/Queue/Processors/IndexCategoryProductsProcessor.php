<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\WizzyProduct;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class IndexCategoryProductsProcessor extends QueueProcessorBase {

   private $productsIndexer;
   private $storeGeneralConfig;
   private $categoriesManager;
   private $categoriesToProcess;
   private $productsToSync;
   private $wizzyProduct;
   private $storeManager;
   private $productsManager;

   public function __construct(IndexerManager $indexerManager, ProductsManager $productsManager, StoreManager $storeManager, StoreGeneralConfig $storeGeneralConfig, CategoriesManager $categoriesManager, WizzyProduct $wizzyProduct) {
      $this->productsIndexer = $indexerManager->getProductsIndexer();
      $this->storeGeneralConfig = $storeGeneralConfig;
      $this->categoriesManager = $categoriesManager;
      $this->categoriesToProcess = [];
      $this->productsToSync = [];
      $this->wizzyProduct = $wizzyProduct;
      $this->storeManager = $storeManager;
      $this->productsManager = $productsManager;
   }

   public function execute(array $data, $storeId) {

      $storeIds = $this->storeManager->getToSyncStoreIds($storeId);

      foreach ($storeIds as $storeId) {
         $this->storeGeneralConfig->setStore($storeId);
         if (!$this->storeGeneralConfig->isSyncEnabled() || !isset($data['categoryIds'])) {
            continue;
         }

         $categoryIds = $data['categoryIds'];
         $categories = $this->categoriesManager->fetch($categoryIds, $storeId);
         if (count($categories)) {
            foreach ($categories as $category) {
               $this->processAllDescendants($category);
            }
            $this->addProductsToSync($storeId);
            $this->wizzyProduct->addProductsInSync($this->productsToSync);
         }
      }

      return TRUE;
   }

   private function addProductsToSync($storeId) {
      $products = $this->productsManager->getProductsByCategoryIds(array_keys($this->categoriesToProcess), $storeId);
      $this->productsToSync = $this->productsManager->getProductIds($products);
   }

   private function processAllDescendants($category) {
      $childCategories = $category->getChildrenCategories();
      foreach ($childCategories as $childCategory) {
         $this->categoriesToProcess[$childCategory->getId()] = $childCategory;
         $this->processAllDescendants($childCategory);
      }
   }
}