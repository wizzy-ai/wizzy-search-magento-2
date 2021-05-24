<?php

namespace Wizzy\Search\Model\Indexer;

use DateTime;
use Wizzy\Search\Services\Catalogue\CatalogRulePriceManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Indexer\ProductPricesHelper;
use Wizzy\Search\Services\Model\ProductPrices;
use Magento;

class ProductsPrices implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{
    private $productPricesHelper;

    public function __construct(
        ProductPricesHelper $productPricesHelper
    ) {
        $this->productPricesHelper = $productPricesHelper;
    }

    public function execute($ids)
    {
        return null;
    }

    public function executeFull()
    {
        $this->productPricesHelper->addUpdatedProductPricesInSync();
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
