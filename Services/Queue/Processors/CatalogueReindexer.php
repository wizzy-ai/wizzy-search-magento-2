<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Store\StoreGeneralConfig;

class CatalogueReindexer extends QueueProcessorBase
{

    private $productsIndexer;
    private $entitiesSync;
    private $productsManager;
    private $storeGeneralConfig;
    private $output;

    public function __construct(
        IndexerManager $indexerManager,
        StoreGeneralConfig $storeGeneralConfig,
        EntitiesSync $entitiesSync,
        ProductsManager $productsManager,
        IndexerOutput $output
    ) {
        $this->productsIndexer = $indexerManager->getProductsIndexer();
        $this->entitiesSync = $entitiesSync;
        $this->productsManager = $productsManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->output = $output;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);
        if (!$this->storeGeneralConfig->isSyncEnabled()) {
            $this->output->writeln(__('Catalogue Reindexer Skipped as Sync is disabled.'));
            return true;
        }
        $this->entitiesSync->markAllEntitiesSynced($storeId, EntitiesSync::ENTITY_TYPE_PRODUCT);
        $productIds = $this->productsManager->getAllProductIds($storeId);
        $this->output->writeln(__('Added '.count($productIds).' Products for processing.'));
        $this->productsIndexer->reindexList($productIds);

        return true;
    }
}
