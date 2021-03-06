<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;
use Wizzy\Search\Helpers\DB\WizzyTables;

class Version131
{
    private $setup;

    public function update(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->createProductPricesTable();
    }

    public function install(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->createProductPricesTable();
    }

    private function createProductPricesTable()
    {
        $conn = $this->setup->getConnection();

        if ($conn->isTableExists(WizzyTables::$PRODUCT_PRICES)) {
            $conn->dropTable(WizzyTables::$PRODUCT_PRICES);
        }

        $entitiesSyncTable = $conn->newTable(WizzyTables::$PRODUCT_PRICES)
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Rule Product Price ID'
            )
            ->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Product ID'
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Product Price Data'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT],
                'Timestamp when rule price added in table.'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => true, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Timestamp when rule price is updated in table.'
            )
            ->setOption('charset', 'utf8');

        $conn->createTable($entitiesSyncTable);
    }
}
