<?php

namespace Wizzy\Search\Model\Indexer;

use Magento;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Queue\QueueManager;

class InvalidateUnresponsiveSyncIndexer implements
    Magento\Framework\Indexer\ActionInterface,
    Magento\Framework\Mview\ActionInterface
{

    private $indexerManager;
    private $queueManager;

    public function __construct(
        IndexerManager $indexerManager,
        QueueManager $queueManager
    ) {
        $this->indexerManager = $indexerManager;
        $this->queueManager = $queueManager;
    }

    public function execute($ids)
    {
        return null;
    }

    public function executeFull()
    {
        $hasInvalidated = $this->indexerManager->invalidateSync();
        if ($hasInvalidated) {
            $this->queueManager->enqueueAllInProgress();
        }
    }

    public function executeList(array $ids)
    {
        return null;
    }

    public function executeRow($id)
    {
        return null;
    }
}
