<?php

namespace Wizzy\Search\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Services\Setup\Version118;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $version118;
    public function __construct(Version118 $version118)
    {
        $this->version118 = $version118;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $this->version118->update($setup);
        }

        $setup->endSetup();
    }
}
