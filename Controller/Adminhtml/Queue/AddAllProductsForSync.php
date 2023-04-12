<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreManager;

class AddAllProductsForSync extends Action
{
    /**
     * @param Context $context
     * @param QueueManager $queueManager
     * @param FlashMessagesManager $flashMessagesManager
     */
    public function __construct(
        Context $context,
        FlashMessagesManager $flashMessagesManager,
        IndexerManager $indexerManager,
        StoreAdvancedConfig $storeAdvancedConfig,
        StoreManager $storeManager
    ) {
        parent::__construct($context);
        $this->flashMessagesManager = $flashMessagesManager;
        $this->indexerManager = $indexerManager;
        $this->storeAdvancedConfig = $storeAdvancedConfig;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/status');
        $productsIndexer = $this->indexerManager->getProductsIndexer();
        $productsIndexer->reindexAll();
        $storeIds = $this->storeManager->getToSyncStoreIds('');
        foreach ($storeIds as $storeId) {
            if ($this->storeAdvancedConfig->hasToAddAllProductsInSync($storeId) == 1) {
                $this->flashMessagesManager->success("Added all the products for Sync for store Id #"
                    . $storeId);
            } else {
                $this->flashMessagesManager->warning("Adding Products in Sync Skipped for Store Id #"
                    . $storeId . " (Based on store's advanced sync configuration)");
            }
        }
        return $resultRedirect;
    }
}
