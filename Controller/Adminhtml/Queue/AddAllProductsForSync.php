<?php

namespace Wizzy\Search\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreManager;

class AddAllProductsForSync extends Action
{
    /**
     * @var IndexerManager
     */
    private $indexerManager;

    /**
     * @var StoreAdvancedConfig
     */
    private $storeAdvancedConfig;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param IndexerManager $indexerManager
     * @param StoreAdvancedConfig $storeAdvancedConfig
     * @param StoreManager $storeManager
     */
    public function __construct(
        Context $context,
        IndexerManager $indexerManager,
        StoreAdvancedConfig $storeAdvancedConfig,
        StoreManager $storeManager
    ) {
        parent::__construct($context);
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
                $this->messageManager->addSuccessMessage(
                    "Added all the products for Sync for store Id #" . $storeId
                );
            } else {
                $this->messageManager->addWarningMessage(
                    "Adding Products in Sync Skipped for Store Id #" . $storeId .
                    " (Based on store's advanced sync configuration)"
                );
            }
        }
        return $resultRedirect;
    }
}
