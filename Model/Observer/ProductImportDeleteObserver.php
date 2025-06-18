<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Wizzy\Search\Services\Indexer\IndexerManager;

class ProductImportDeleteObserver implements ObserverInterface
{
    private $indexer;
    public function __construct(
        IndexerManager $indexerManager
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
    }

    public function execute(Observer $observer)
    {
        $productIdsToDelete = $observer->getData('ids_to_delete');
        if (count($productIdsToDelete) && !$this->indexer->isScheduled()) {
            $this->indexer->reindexList($productIdsToDelete);
        }
    }
}
