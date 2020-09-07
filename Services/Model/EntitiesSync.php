<?php

namespace Wizzy\Search\Services\Model;

use Magento\Framework\App\ResourceConnection;
use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Model\EntitiesSyncFactory;
use Wizzy\Search\Services\DB\ConnectionManager;

class EntitiesSync
{

    public static $ENTITY_IN_SYNC_STATUS = 0;
    public static $ENTITY_SYNCED_STATUS = 1;
    const ENTITY_TYPE_PRODUCT = 'product';

    private $entitiesSyncFactory;
    private $connectionManager;

    public function __construct(EntitiesSyncFactory $entitiesSyncFactory, ConnectionManager $connectionManager)
    {
        $this->entitiesSyncFactory = $entitiesSyncFactory;
        $this->connectionManager = $connectionManager;
    }

    public function filterEntitiesYetToSync($entityIds, $storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        $entitesSync = $this->entitiesSyncFactory->create();
        $entitiesPresent = $entitesSync->getCollection()
        ->addFieldToFilter('entity_type', ['eq' => $entityType])
        ->addFieldToFilter('entity_id', ['IN' => $entityIds])
        ->addFieldToFilter('store_id', ['eq' => $storeId])
        ->getItems();

        $entityIdsNotAllowedToSync = [];

        foreach ($entitiesPresent as $entityPresent) {
            $entityData = $entityPresent->getData();
            $entityId = $entityData['entity_id'];
            $entityStatus = $entityData['status'];

            if ($entityStatus == self::$ENTITY_IN_SYNC_STATUS) {
                $entityIdsNotAllowedToSync[] = $entityId;
            }
        }

        return array_diff($entityIds, $entityIdsNotAllowedToSync);
    }

    public function hasAnyEntitiesAddedInSync($storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        $entitesSync = $this->entitiesSyncFactory->create();
        $entitiesPresent = $entitesSync->getCollection()
           ->addFieldToFilter('entity_type', ['eq' => $entityType])
           ->addFieldToFilter('store_id', ['eq' => $storeId])
           ->getItems();

        return (count($entitiesPresent) > 0);
    }

    public function addEntitiesToSync($entityIds, $storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        $recordsToInsert = [];
        foreach ($entityIds as $entityId) {
            $recordsToInsert[] = [
            'entity_id'   => $entityId,
            'store_id'    => $storeId,
            'entity_type' => $entityType,
            'status'      => self::$ENTITY_IN_SYNC_STATUS,
            ];
        }

        $this->connectionManager->insertMultiple(WizzyTables::$ENTITIES_SYNC_TABLE_NAME, $recordsToInsert);
    }

    public function markEntitiesAsSynced($entityIds, $storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        $recordsToInsert = [];

        foreach ($entityIds as $entityId) {
            $recordsToInsert[] = [
            'entity_id'   => $entityId,
            'store_id'    => $storeId,
            'entity_type' => $entityType,
            'status'      => self::$ENTITY_SYNCED_STATUS,
            ];
        }

        $this->connectionManager->insertMultiple(WizzyTables::$ENTITIES_SYNC_TABLE_NAME, $recordsToInsert);
    }

    public function markAllEntitiesSynced($storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        $entitesSync = $this->entitiesSyncFactory->create();
        $entitiesPresent = $entitesSync->getCollection()
        ->addFieldToFilter('entity_type', ['eq' => $entityType])
        ->addFieldToFilter('store_id', ['eq' => $storeId])
        ->getItems();

        $entityIds = [];
        foreach ($entitiesPresent as $entity) {
            $entityData = $entity->getData();
            $entityIds[] = $entityData['entity_id'];
        }

        $this->markEntitiesAsSynced($entityIds, $storeId, $entityType);
    }
}
