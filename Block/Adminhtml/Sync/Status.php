<?php

namespace Wizzy\Search\Block\Adminhtml\Sync;

use Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use Wizzy\Search\Services\Model\EntitiesSync;
use Magento\Store\Model\StoreManagerInterface;
use Wizzy\Search\Services\Store\StoreManager;
use Magento\Framework\View\Element\Template;

class Status extends Template
{
    protected $_template = 'sync/status.phtml';
    public function __construct(
        EntitiesSync $entitiesSync,
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        StoreManager $_storemanager,
        array $data = []
    ) {
        $this->entitiesSync = $entitiesSync;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->_storemanager = $_storemanager;
        parent::__construct($context, $data);
    }

    public function getSyncStatus()
    {
        $entityId = $this->registry->registry('current_product')->getId();
        $storeId = $this->storeManager->getStore()->getId();
        $storeIds = $this->_storemanager->getToSyncStoreIds($storeId);
        $syncResults = [];

        foreach ($storeIds as $storeId) {
            $entityStatus = $this->entitiesSync->getEntitiesSyncStatus($entityId, $storeId, 'product');
            if (count($entityStatus) == 0) {
                $entityStatus = [
                    [
                    "store_id" => $storeId,
                    "status" => -1,
                    "updated_at" => "&nbsp;-"
                    ]
                ];
            }
            $syncResults[] = $entityStatus;
        }

        return $syncResults;
    }
}
