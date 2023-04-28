<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;

use Wizzy\Search\Services\Indexer\IndexerManager;

class ImportProductsObserver implements ObserverInterface
{
    private $indexer;
    public function __construct(
        IndexerManager $indexerManager,
        ProductsManager $productsManager,
        Configurable $configurable
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->productsManager = $productsManager;
        $this->configurable = $configurable;
    }
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getData('bunch');
        $SKUs = array_column($bunch, 'sku');
        $products = $this->productsManager->getProductsBySKUs($SKUs);
        $productIds = $this->productsManager->getProductIds($products);
        $parentProductIds = $this->configurable->getParentIdsByChild($productIds);

        $productIdsToBeSynced = array_unique(array_merge($productIds, $parentProductIds));

        if (count($productIdsToBeSynced) > 0) {
            $this->indexer->reindexList($productIdsToBeSynced);
        }
    }
}
