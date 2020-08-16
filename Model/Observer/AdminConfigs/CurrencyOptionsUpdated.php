<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Wizzy\Search\Services\Indexer\IndexerManager;

class CurrencyOptionsUpdated implements ObserverInterface
{
    private $indexerManager;

    public function __construct(
        IndexerManager $indexerManager
    ) {
        $this->indexerManager = $indexerManager;
    }

    public function execute(EventObserver $observer)
    {
        $this->indexerManager->getCurrenciesIndexer()->reindexList([]);
        return $this;
    }
}
