<?php

namespace Wizzy\Search\Services\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\StateInterface;

class IndexerManager
{

    private $indexer;

    public function __construct(IndexerRegistry $indexerRegistry)
    {
        $this->indexer = $indexerRegistry;
    }

    public function get($indexer)
    {
        return $this->indexer->get($indexer);
    }

    public function getProductsIndexer()
    {
        return $this->get('wizzy_products_indexer');
    }

    public function getCurrenciesIndexer()
    {
        return $this->get('wizzy_currencies_indexer');
    }

    public function getPagesIndexer()
    {
        return $this->get('wizzy_pages_indexer');
    }

    public function getInvalidateIndexer()
    {
        return $this->get('wizzy_invalidate_unresponsive_sync_indexer');
    }

    public function getSyncIndexer()
    {
        return $this->get('wizzy_sync_queue_runner_indexer');
    }

    public function invalidateProductsIndexer()
    {
        $productsIndexer = $this->getProductsIndexer();
        return $this->isStuck($productsIndexer);
    }

    public function invalidateSync()
    {
        $syncIndexer = $this->getSyncIndexer();
        return $this->isStuck($syncIndexer);
    }

    private function isStuck($indexer)
    {
        $latestUpdated = $indexer->getLatestUpdated();

        if ($latestUpdated != "") {
            $latestUpdated = \DateTime::createFromFormat("Y-m-d H:i:s", $latestUpdated);
            $now = (new \DateTime())->modify("-1 hour");

            if ($latestUpdated <= $now &&
                $indexer->getStatus() == StateInterface::STATUS_WORKING) {
                $indexer->invalidate();
                return true;
            }
        }

        return false;
    }
}
