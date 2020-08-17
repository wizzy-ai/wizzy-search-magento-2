<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Store\StoreGeneralConfig;

class CatalogueReindexer extends QueueProcessorBase
{

    private $productsIndexer;
    private $entitiesSync;
    private $productsManager;
    private $storeGeneralConfig;

    public function __construct(
        IndexerManager $indexerManager,
        StoreGeneralConfig $storeGeneralConfig,
        EntitiesSync $entitiesSync,
        ProductsManager $productsManager
    ) {
        $this->productsIndexer = $indexerManager->getProductsIndexer();
        $this->entitiesSync = $entitiesSync;
        $this->productsManager = $productsManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
    }

    public function execute(array $data, $storeId)
    {
        $this->storeGeneralConfig->setStore($storeId);
        if (!$this->storeGeneralConfig->isSyncEnabled()) {
            return true;
        }
        $this->entitiesSync->markAllEntitiesSynced($storeId, EntitiesSync::ENTITY_TYPE_PRODUCT);
        $productIds = $this->productsManager->getAllProductIds();
        $this->productsIndexer->reindexList($productIds);

        return true;
    }
}
