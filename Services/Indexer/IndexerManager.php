<?php

namespace Wizzy\Search\Services\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;

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
}
