<?php

namespace Wizzy\Search\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Wizzy\Search\Services\Setup\Version320;

class PatchV320 implements DataPatchInterface
{
    private $version320;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        version320 $version320
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->version320 = $version320;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->version320->update();
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
