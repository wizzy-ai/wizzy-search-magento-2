<?php

namespace Wizzy\Search\Services\Model;

use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Model\SyncSkippedEntitiesFactory;
use Wizzy\Search\Services\DB\ConnectionManager;

class SyncSkippedEntities
{
    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_PAGE = 'page';

    private $syncSkippedEntitiesFactory;
    private $connectionManager;

    public function __construct(
        SyncSkippedEntitiesFactory $syncSkippedEntitiesFactory,
        ConnectionManager $connectionManager
    ) {
        $this->syncSkippedEntitiesFactory = $syncSkippedEntitiesFactory;
        $this->connectionManager = $connectionManager;
    }

    public function addSkippedEntities($skippedEntities, $storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        if (count($skippedEntities) === 0) {
            return true;
        }

        $recordsToInsert = [];
        foreach ($skippedEntities as $skippedEntity) {
            $recordsToInsert[] = [
            'entity_id'   => $skippedEntity['id'],
            'store_id'    => $storeId,
            'entity_type' => $entityType,
            'entity_data' => $skippedEntity['data'],
            ];
        }

        $this->connectionManager->insertMultiple(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME, $recordsToInsert);
    }

    public function deleteSkippedEntities($entityIds, $storeId, $entityType = self::ENTITY_TYPE_PRODUCT)
    {
        if (count($entityIds) === 0) {
            return true;
        }

        $entities = $this->syncSkippedEntitiesFactory->create()->getCollection()
         ->addFieldToFilter('entity_id', ["in" => $entityIds])
         ->addFieldToFilter('store_id', ["eq" => $storeId])
         ->addFieldToFilter('entity_type', ["eq" => $entityType]);

        $entities = $entities->setOrder('id', 'ASC');
        $entities->walk('delete');

        return $entities;
    }
}
