<?php

namespace Wizzy\Search\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Wizzy\Search\Services\Setup\Version246;

class PatchV246 implements DataPatchInterface
{
    private $version246;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Version246 $version246
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->version246 = $version246;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->version246->update();
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
