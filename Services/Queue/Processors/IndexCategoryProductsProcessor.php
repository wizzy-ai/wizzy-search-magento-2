<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Model\WizzyProduct;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class IndexCategoryProductsProcessor extends QueueProcessorBase
{

    private $productsIndexer;
    private $storeGeneralConfig;
    private $categoriesManager;
    private $categoriesToProcess;
    private $productsToSync;
    private $wizzyProduct;
    private $storeManager;
    private $productsManager;
    private $output;

    public function __construct(
        IndexerManager $indexerManager,
        ProductsManager $productsManager,
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        CategoriesManager $categoriesManager,
        WizzyProduct $wizzyProduct,
        IndexerOutput $output
    ) {
        $this->productsIndexer = $indexerManager->getProductsIndexer();
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->categoriesManager = $categoriesManager;
        $this->categoriesToProcess = [];
        $this->productsToSync = [];
        $this->wizzyProduct = $wizzyProduct;
        $this->storeManager = $storeManager;
        $this->productsManager = $productsManager;
        $this->output = $output;
    }

    public function execute(array $data, $storeId)
    {
        if ($storeId == 0) {
            $storeIds = $this->storeManager->getToSyncStoreIds();
        } else {
            $storeIds = [$storeId];
        }

        foreach ($storeIds as $storeId) {
            $this->storeGeneralConfig->setStore($storeId);

            if (!$this->storeGeneralConfig->isSyncEnabled() || !isset($data['categoryIds'])) {
                if (!$this->storeGeneralConfig->isSyncEnabled()) {
                    $this->output->writeln(__('Category Products Indexing Skipped as Sync is disabled
                    for Store ID: %1', $storeId));
                }
                continue;
            }

            $categoryIds = $data['categoryIds'];
            $this->output->writeln(__(
                'Started processing %1 categories for Store ID: %2',
                count($categoryIds),
                $storeId
            ));

            $categories = $this->categoriesManager->fetch($categoryIds, $storeId);

            if (count($categories)) {
                foreach ($categories as $category) {
                    $this->processAllDescendants($category);
                }
                $this->addProductsToSync($storeId);
                $this->wizzyProduct->addProductsInSync($this->productsToSync, $storeId);
            }
        }

        return true;
    }

    private function addProductsToSync($storeId)
    {
        $productIds = $this->productsManager->getProductsByCategoryIds(
            array_keys($this->categoriesToProcess),
            $storeId
        );
        $this->productsToSync = $productIds;
    }

    private function processAllDescendants($category)
    {
        $childCategories = $category->getChildrenCategories();
        $this->categoriesToProcess[$category->getId()] = $category;
        foreach ($childCategories as $childCategory) {
            $this->categoriesToProcess[$childCategory->getId()] = $childCategory;
            $this->processAllDescendants($childCategory);
        }
    }
}
