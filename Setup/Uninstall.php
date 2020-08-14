<?php

namespace Wizzy\Search\Setup;
 
use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Wizzy\Search\Helpers\DB\WizzyTables;

class Uninstall implements UninstallInterface {
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
      $setup->startSetup();
      $conn = $setup->getConnection();

      if ($conn->isTableExists(WizzyTables::$SYNC_QUEUE_TABLE_NAME)) {
        $conn->dropTable(WizzyTables::$SYNC_QUEUE_TABLE_NAME);
      }

      if ($conn->isTableExists(WizzyTables::$PRODUCTS_SYNC_TABLE_NAME)) {
        $conn->dropTable(WizzyTables::$PRODUCTS_SYNC_TABLE_NAME);
      }

      $setup->endSetup();
    }
}