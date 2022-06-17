<?php

namespace Wizzy\Search\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Services\DB\ConnectionManager;

class Uninstall implements UninstallInterface
{
    private $connectionManager;

    public function __construct(
        ConnectionManager $connectionManager
    ) {
        $this->connectionManager = $connectionManager;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $conn = $setup->getConnection();

        $syncQueueTable = $this->connectionManager->getTableName(WizzyTables::$SYNC_QUEUE_TABLE_NAME);
        $entitiesTable = $this->connectionManager->getTableName(WizzyTables::$ENTITIES_SYNC_TABLE_NAME);

        if ($conn->isTableExists($syncQueueTable)) {
            $conn->dropTable($syncQueueTable);
        }

        if ($conn->isTableExists($entitiesTable)) {
            $conn->dropTable($entitiesTable);
        }

        $setup->endSetup();
    }
}
