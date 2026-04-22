<?php

namespace Wizzy\Search\Cron;

use Magento\Indexer\Model\IndexerFactory;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;

class RecoverStaleEntitiesIndexer
{
    /**
     * @var IndexerFactory
     */
    protected $indexerFactory;
    protected $storeAdvancedConfig;

    public function __construct(
        IndexerFactory $indexerFactory,
        StoreAdvancedConfig $storeAdvancedConfig
    ) {
        $this->indexerFactory = $indexerFactory;
        $this->storeAdvancedConfig = $storeAdvancedConfig;
    }

    public function getIndexer($indexerId)
    {
        return $this->indexerFactory->create()->load($indexerId);
    }

    public function execute()
    {
        if (!$this->storeAdvancedConfig->hasToEnableRecoverStaleEntities()) {
            return;
        }

        $this->getIndexer('wizzy_recover_stale_entities_indexer')->reindexAll();
    }
}
