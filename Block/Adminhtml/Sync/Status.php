<?php

namespace Wizzy\Search\Block\Adminhtml\Sync;

use Magento\Backend\Block\Template\Context;
use \Magento\Framework\Registry;
use Wizzy\Search\Services\Model\EntitiesSync;
use Magento\Store\Model\StoreManagerInterface;
use Wizzy\Search\Services\Store\StoreManager;
use Magento\Framework\View\Element\Template;
use Wizzy\Search\Services\Model\SyncSkippedEntities;

class Status extends Template
{
    protected $_template = 'sync/status.phtml';
    public $syncSkippedEntities;
    public function __construct(
        EntitiesSync $entitiesSync,
        Context $context,
        Registry $registry,
        StoreManagerInterface $storeManager,
        StoreManager $_storemanager,
        SyncSkippedEntities $syncSkippedEntities,
        array $data = []
    ) {
        $this->entitiesSync = $entitiesSync;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->_storemanager = $_storemanager;
        $this->syncSkippedEntities = $syncSkippedEntities;
        parent::__construct($context, $data);
    }

    public function getSyncStatus()
    {
        $entityId = $this->registry->registry('current_product')->getId();
        $storeId = $this->storeManager->getStore()->getId();
        $storeIds = $this->_storemanager->getToSyncStoreIds($storeId);
        $entitiesSyncStatus_ = [];
        foreach ($storeIds as $storeId) {
            $entitiesSyncStatus = $this->entitiesSync->getEntitiesSyncStatus($entityId, $storeId, 'product');
            $skippedEntitiesData = $this->syncSkippedEntities->getSkippedEntities($storeId, $entityId, 'product');

            if ($entitiesSyncStatus) {
                foreach ($entitiesSyncStatus as $entitySyncStatus) {
                    $entitiesSyncStatus_[] = [
                        "id" => $entitySyncStatus['id'],
                        "entity_id" => $entitySyncStatus['entity_id'],
                        "entity_type" => $entitySyncStatus['entity_type'],
                        "store_id" => $entitySyncStatus['store_id'],
                        "status" => $entitySyncStatus['status'],
                        "last_synced_at" => $entitySyncStatus['last_synced_at'],
                        "created_at" => $entitySyncStatus['created_at'],
                        "updated_at" => $entitySyncStatus['updated_at'],
                        "skipped_data" => ($skippedEntitiesData && !empty($skippedEntitiesData['entity_data']))
                        ? $skippedEntitiesData['entity_data']
                        : "",
                    ];
                }
            } else {
                $entitiesSyncStatus_[] = [
                    "store_id" => $storeId,
                    "status" => -1,
                    "updated_at" => "-"
                ];
            }

        }
        return [$entitiesSyncStatus_];
    }
}
