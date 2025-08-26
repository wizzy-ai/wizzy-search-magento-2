<?php

namespace Wizzy\Search\Services\Model;

use Magento\Framework\Indexer\IndexerRegistry;
use Wizzy\Search\Services\DB\ConnectionManager;
use Wizzy\Search\Model\Indexer\Products;

class WizzyProduct
{

    private $connectionManager;
    private $indexerRegistry;
    private $productsIndexer;
    private $_productsIndexer;

    public function __construct(
        ConnectionManager $connectionManager,
        IndexerRegistry $indexerRegistry,
        Products $_productsIndexer
    ) {
        $this->connectionManager = $connectionManager;
        $this->indexerRegistry = $indexerRegistry;
        $this->_productsIndexer = $_productsIndexer;
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

    public function addProductsInSync(array $productIds, $storeId = null)
    {
        if (!$this->productsIndexer->isScheduled()) {
            $this->_productsIndexer->setStoreId($storeId);
            $this->_productsIndexer->executeList($productIds);
        } else {
            $this->addProductsInChangeLog($productIds);
        }
    }
}
