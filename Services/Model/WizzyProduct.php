<?php

namespace Wizzy\Search\Services\Model;

use Magento\Framework\Indexer\IndexerRegistry;
use Wizzy\Search\Services\DB\ConnectionManager;

class WizzyProduct
{

    private $connectionManager;
    private $indexerRegistry;
    private $productsIndexer;

    public function __construct(ConnectionManager $connectionManager, IndexerRegistry $indexerRegistry)
    {
        $this->connectionManager = $connectionManager;
        $this->indexerRegistry = $indexerRegistry;

        $this->productsIndexer = $indexerRegistry->get('wizzy_products_indexer');
    }

    public function addProductsInChangeLog(array $productIds)
    {
        $view = $this->productsIndexer->getView();
        $changelogTableName = $this->connectionManager->getTableName($view->getChangelog()->getName());

        $data = [];
        foreach ($productIds as $productId) {
            $data[] = ['entity_id' => $productId];
        }

        $this->connectionManager->insertMultiple($changelogTableName, $data, false);
    }

    public function addProductsInSync(array $productIds)
    {
        if (!$this->productsIndexer->isScheduled()) {
            $this->productsIndexer->reindexList($productIds);
        } else {
            $this->addProductsInChangeLog($productIds);
        }
    }
}
