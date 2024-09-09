<?php
namespace Wizzy\Search\Model\Observer;

use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Model\Observer\ProductsObserver;

class BaseOrdersObserver
{
    private $indexer;
    private $productsObserver;

    public function __construct(
        IndexerManager $indexerManager,
        ProductsObserver $productsObserver
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->productsObserver = $productsObserver;
    }

    public function addOrderProductsInSync($order)
    {
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            if (!$this->indexer->isScheduled()) {
                $productIdsToSync = $this->productsObserver->getProductIdsToIndex([$productId]);
                $this->indexer->reindexList($productIdsToSync);
            }
        }
    }
}
