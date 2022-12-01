<?php

namespace Wizzy\Search\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Wizzy\Search\Services\Setup\Version234;

class PatchV234 implements DataPatchInterface
{
    private $version234;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Version234 $version234
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->version234 = $version234;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->version234->update();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
