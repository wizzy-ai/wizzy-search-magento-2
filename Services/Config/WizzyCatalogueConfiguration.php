<?php

namespace Wizzy\Search\Services\Config;

use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\Processors\IndexProductsProcessor;
use Wizzy\Search\Services\Queue\QueueManager;

class WizzyCatalogueConfiguration
{

    private $queueManager;
    public function __construct(QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;
    }

    public function clearProductIndexingJobs($storeId)
    {
        $this->queueManager->clear($storeId, CatalogueReindexer::class);
        $this->queueManager->clear($storeId, IndexProductsProcessor::class);
    }
}
