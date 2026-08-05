<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Catalog\Model\Product\Visibility;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Store\Model\App\Emulation;
use Wizzy\Search\Services\API\Wizzy\Modules\Products as ProductsApi;
use Wizzy\Search\Services\Catalogue\Mappers\ProductPricesMapper;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\SessionStorage\ProductsSessionStorage;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

/**
 * Loads products, maps prices, and saves them to Wizzy prices API on indexer run.
 */
class ProductPricesSyncManager
{
    const BATCH_SIZE = 1000;

    private $storeManager;
    private $storeGeneralConfig;
    private $productsManager;
    private $productPricesMapper;
    private $productsApi;
    private $productsSessionStorage;
    private $output;
    private $emulation;
    private $scopeCodeResolver;

    public function __construct(
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        ProductsManager $productsManager,
        ProductPricesMapper $productPricesMapper,
        ProductsApi $productsApi,
        ProductsSessionStorage $productsSessionStorage,
        IndexerOutput $output,
        Emulation $emulation,
        ScopeCodeResolver $scopeCodeResolver
    ) {
        $this->storeManager = $storeManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->productsManager = $productsManager;
        $this->productPricesMapper = $productPricesMapper;
        $this->productsApi = $productsApi;
        $this->productsSessionStorage = $productsSessionStorage;
        $this->output = $output;
        $this->emulation = $emulation;
        $this->scopeCodeResolver = $scopeCodeResolver;
    }

    /**
     * Sync prices for all sync-enabled stores (full reindex).
     */
    public function syncAllStores()
    {
        $storeIds = $this->storeManager->getToSyncStoreIds('');

        foreach ($storeIds as $storeId) {
            $this->syncStore($storeId);
        }
    }

    /**
     * Sync prices for a single store.
     *
     * @param int|string $storeId
     * @return bool
     */
    public function syncStore($storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);

        if (!$this->storeGeneralConfig->isSyncEnabled()) {
            $this->output->writeln(__('Bulk Product Prices Sync Skipped for Store #'
                . $storeId . ' as Sync is disabled.'));
            return true;
        }

        $this->output->writeDiv();
        $this->output->writeln(__('Started Bulk Product Prices Sync for Store #' . $storeId));

        $productIds = $this->productsManager->getAllProductIds($storeId);
        $this->output->writeln(__('Found ' . count($productIds) . ' enabled Products for Store #' . $storeId));

        if (count($productIds) == 0) {
            $this->output->writeln(__('No Products to sync prices for Store #' . $storeId));
            return true;
        }

        $this->startEmulation($storeId);

        try {
            $batches = array_chunk($productIds, self::BATCH_SIZE);
            $totalSaved = 0;

            foreach ($batches as $batchIndex => $batchProductIds) {
                $batchNumber = $batchIndex + 1;
                $this->output->writeln(__(
                    'Processing price batch #' . $batchNumber . ' (' . count($batchProductIds) . ' Products)'
                ));

                $products = $this->productsManager->getProductsByIds($batchProductIds, $storeId);
                $this->setSessionData($products, $batchProductIds, $storeId);

                $products = $this->filterActiveProducts($products);
                $mappedPrices = $this->productPricesMapper->mapAll($products, $storeId);

                if (count($mappedPrices) == 0) {
                    $this->output->writeln(__('No Product Prices to save in batch #' . $batchNumber));
                    unset($products, $mappedPrices);
                    $this->productsSessionStorage->set([]);
                    continue;
                }

                $this->output->writeln(__('Saving ' . count($mappedPrices) . ' Product Prices.'));
                $saveResponse = $this->productsApi->savePrices($mappedPrices, $storeId);

                if ($saveResponse !== true) {
                    $this->output->writeln(__('Failed saving Product Prices for Store #' . $storeId
                        . ' in batch #' . $batchNumber));
                    unset($products, $mappedPrices);
                    $this->productsSessionStorage->set([]);
                    return false;
                }

                $totalSaved += count($mappedPrices);
                $this->output->writeln(__('Saved ' . count($mappedPrices) . ' Product Prices successfully.'));
                unset($products, $mappedPrices);
                $this->productsSessionStorage->set([]);
            }

            $this->output->writeln(__('Completed Bulk Product Prices Sync for Store #' . $storeId
                . '. Total saved: ' . $totalSaved));

            return true;
        } finally {
            $this->stopEmulation();
        }
    }

    /**
     * Same filter as IndexProductsProcessor::findDeletedAndActiveProducts —
     * keep enabled products that are not Catalog-only visibility.
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @return \Magento\Catalog\Model\Product[]
     */
    private function filterActiveProducts($products)
    {
        $activeProducts = [];

        foreach ($products as $product) {
            if (!$product->isDisabled() &&
                $product->getVisibility() != ((string)Visibility::VISIBILITY_IN_CATALOG)
            ) {
                $activeProducts[] = $product;
            }
        }

        return $activeProducts;
    }

    /**
     * Populate products session storage with batch products plus parents/children
     * (required for groupId and configurable childData), same approach as product sync.
     *
     * @param \Magento\Catalog\Model\Product[] $products
     * @param array $productIds
     * @param int|string $storeId
     */
    private function setSessionData($products, $productIds, $storeId)
    {
        $productObjectByIds = $this->getProductObjectByIds($products, $productIds, $storeId);
        $this->productsSessionStorage->set($productObjectByIds);
    }

    /**
     * @param \Magento\Catalog\Model\Product[] $products
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
     * @param \Magento\Catalog\Model\Product[] $products
     * @param array $productIds
     * @param int|string $storeId
     * @return array
     */
    private function getProductObjectByIds($products, $productIds, $storeId)
    {
        $parentProductIds = $this->productsManager->getParentProductIds($productIds);
        $childProductIds = $this->getChildProductIds($products);
        $productIdsToQuery = array_merge($parentProductIds, $childProductIds);

        $productsToMerge = [];
        if (count($productIdsToQuery) > 0) {
            $productsToMerge = $this->productsManager->getProductsByIds($productIdsToQuery, $storeId);
        }

        $productObjectByIds = [];

        foreach ($products as $product) {
            $productObjectByIds[$product->getId()] = $product;
        }

        foreach ($productsToMerge as $productToMerge) {
            $productObjectByIds[$productToMerge->getId()] = $productToMerge;
        }

        return $productObjectByIds;
    }

    private function startEmulation($storeId)
    {
        $this->emulation->startEnvironmentEmulation($storeId, Area::AREA_FRONTEND, true);
        $this->scopeCodeResolver->clean();
    }

    private function stopEmulation()
    {
        $this->emulation->stopEnvironmentEmulation();
    }
}
