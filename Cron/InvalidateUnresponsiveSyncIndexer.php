<?php

namespace Wizzy\Search\Cron;

use Magento\Indexer\Model\IndexerFactory;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;

class InvalidateUnresponsiveSyncIndexer
{
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
        if (!$this->storeAdvancedConfig->hasToEnableInvalidateUnresponsiveSync()) {
            return;
        }

        $this->getIndexer('wizzy_invalidate_unresponsive_sync_indexer')->reindexAll();
    }
}
