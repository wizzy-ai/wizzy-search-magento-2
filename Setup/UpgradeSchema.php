<?php

namespace Wizzy\Search\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Wizzy\Search\Services\Setup\Version118;
use Wizzy\Search\Services\Setup\Version125;
use Wizzy\Search\Services\Setup\Version130;
use Wizzy\Search\Services\Setup\Version131;
use Wizzy\Search\Services\Setup\Version135;

class UpgradeSchema implements UpgradeSchemaInterface
{
    private $version118;
    private $version125;
    private $version130;
    private $version131;
    private $version135;

    public function __construct(
        Version118 $version118,
        Version125 $version125,
        Version130 $version130,
        Version131 $version131,
        Version135 $version135
    ) {
        $this->version118 = $version118;
        $this->version125 = $version125;
        $this->version130 = $version130;
        $this->version131 = $version131;
        $this->version135 = $version135;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $this->version118->update($setup);
        }

        if (version_compare($context->getVersion(), '1.2.5', '<')) {
            $this->version125->update($setup);
        }

        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            $this->version130->update($setup);
        }

        if (version_compare($context->getVersion(), '1.3.1', '<')) {
            $this->version131->update($setup);
        }

        if (version_compare($context->getVersion(), '1.3.5', '<')) {
            $this->version135->update($setup);
        }

        $setup->endSetup();
    }
}
