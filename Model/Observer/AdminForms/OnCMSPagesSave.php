<?php

namespace Wizzy\Search\Model\Observer\AdminForms;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Queue\Processors\DeletePagesProcessor;
use Wizzy\Search\Services\Queue\QueueManager;

class OnCMSPagesSave implements ObserverInterface
{

    private $request;
    private $indexerManager;

    public function __construct(
        RequestInterface $request,
        IndexerManager $indexerManager
    ) {
        $this->request = $request;
        $this->indexerManager = $indexerManager;
    }

    public function execute(EventObserver $observer)
    {
        $this->indexerManager->getPagesIndexer()->reindexList([]);
        return $this;
    }
}
