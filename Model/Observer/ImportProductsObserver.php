<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;

class ImportProductsObserver implements ObserverInterface
{
    private $indexer;
    public function __construct(
        IndexerManager $indexerManager,
        ProductsManager $productsManager
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->productsManager = $productsManager;
    }
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getData('bunch');
        $SKUs = array_column($bunch, 'sku');
        $products = $this->productsManager->getProductsBySKUs($SKUs);
        $productIds = $this->productsManager->getProductIds($products);
        if (count($productIds) > 0) {
            $this->indexer->reindexList($productIds);
        }
    }
}
