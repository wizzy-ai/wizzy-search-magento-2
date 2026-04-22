<?php

namespace Wizzy\Search\Cron;

use Magento\Indexer\Model\IndexerFactory;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;

class ProductsPricesIndexer
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
        if (!$this->storeAdvancedConfig->hasToEnableProductsPricesIndexer()) {
            return;
        }

        $this->getIndexer('wizzy_products_prices_indexer')->reindexAll();
    }
}
