<?php

namespace Wizzy\Search\Plugin\Import;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Inventory\Model\ResourceModel\SourceItem\SaveMultiple;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Model\Observer\ImportProductsObserver;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Queue\Processors\AddImportedProductsInQueueProcessor;
use Magento\Framework\App\RequestInterface;

class AfterSourceItemsUpdated
{
    private $indexer;
    private $configurable;
    private $productsManager;
    private $importProductsObserver;
    private $queueManager;
    private $storeManager;
    private $request;

    public function __construct(
        Configurable $configurable,
        ProductsManager $productsManager,
        IndexerManager $indexerManager,
        RequestInterface $request,
        ImportProductsObserver $importProductsObserver,
        QueueManager $queueManager,
        StoreManager $storeManager
    ) {
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->configurable = $configurable;
        $this->productsManager = $productsManager;
        $this->importProductsObserver = $importProductsObserver;
        $this->queueManager = $queueManager;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function afterExecute(
        SaveMultiple $subject,
        $result,
        array $sourceItems
    ): void {
        $fullActionName = $this->request->getFullActionName();
        $entity = $this->request->getParam('entity');

        if ($fullActionName !== 'adminhtml_import_start' || $entity !== 'stock_sources') {
            return;
        }

        $skus = array_unique(array_map(function ($item) {
            return $item->getSku();
        }, $sourceItems));

        if (empty($skus)) {
            return;
        }

        $storeIds = $this->storeManager->getToSyncStoreIds();
        if (!$storeIds) {
            return;
        }

        foreach ($storeIds as $storeId) {
            $data = $this->importProductsObserver->createSkuFile($skus, $storeId);
            $this->queueManager->enqueue(
                AddImportedProductsInQueueProcessor::class,
                $storeId,
                $data
            );
        }
    }
}
