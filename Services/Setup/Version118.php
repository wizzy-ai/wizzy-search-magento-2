<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Wizzy\Search\Helpers\DB\WizzyTables;

class Version118
{

    private $setup;

    public function install(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->createSyncSkippedEntitiesTable();
    }

    public function update(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->createSyncSkippedEntitiesTable();
    }

    private function createSyncSkippedEntitiesTable()
    {
        $conn = $this->setup->getConnection();

        if ($conn->isTableExists(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME)) {
            $conn->dropTable(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME);
        }

        $entitiesSyncTable = $conn->newTable(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME)
         ->addColumn(
             'id',
             Table::TYPE_INTEGER,
             null,
             ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true],
             'Unique Identifier'
         )
         ->addColumn(
             'entity_id',
             Table::TYPE_INTEGER,
             null,
             ['unsigned'=>true,'nullable'=>false],
             'Entity ID'
         )
         ->addColumn(
             'entity_type',
             Table::TYPE_TEXT,
             255,
             ['nullable' => false, 'default' => 'product'],
             'Entity Type'
         )
         ->addColumn(
             'entity_data',
             Table::TYPE_TEXT,
             null,
             ['nullable' => false],
             'Entity Data'
         )
         ->addColumn(
             'store_id',
             Table::TYPE_INTEGER,
             null,
             ['unsigned'=>true,'nullable'=>false],
             'Store ID'
         )
         ->addColumn(
             'created_at',
             Table::TYPE_TIMESTAMP,
             null,
             ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
             'Timestamp when entity is added in skipped table.'
         )
         ->addColumn(
             'updated_at',
             Table::TYPE_TIMESTAMP,
             null,
             ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
             'Timestamp when entity is updated in skipped table.'
         )
         ->setOption('charset', 'utf8');

        $conn->createTable($entitiesSyncTable);

        $conn->addIndex(
            $this->setup->getTable(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME),
            $this->setup->getIdxName(
                $this->setup->getTable(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME),
                ['entity_id','entity_type', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['entity_id','entity_type', 'store_id'],
            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
