<?php

namespace Wizzy\Search\Model\Indexer;

use Magento;
use Wizzy\Search\Services\Catalogue\ProductPricesSyncManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;

/**
 * Full reindex only: load enabled products, map prices, save to Wizzy prices API.
 * Does not use the sync queue. Does not change existing product/price indexers.
 */
class BulkProductPrices implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{
    private $productPricesSyncManager;
    private $output;

    public function __construct(
        ProductPricesSyncManager $productPricesSyncManager,
        IndexerOutput $output
    ) {
        $this->productPricesSyncManager = $productPricesSyncManager;
        $this->output = $output;
    }

    /**
     * Partial reindex not supported yet.
     */
    public function execute($ids)
    {
        return $this;
    }

    public function executeFull()
    {
        $this->output->writeDiv();
        $this->output->writeln(__('Running Wizzy Bulk Product Prices Indexer'));
        $this->productPricesSyncManager->syncAllStores();
        return $this;
    }

    /**
     * Partial reindex not supported yet.
     */
    public function executeList(array $ids)
    {
        return $this;
    }

    /**
     * Partial reindex not supported yet.
     */
    public function executeRow($id)
    {
        return $this;
    }
}
