<?php

namespace Wizzy\Search\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Wizzy\Search\Services\Setup\Version326;

class PatchV326 implements DataPatchInterface
{
    private $version326;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Version326 $version326
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->version326 = $version326;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->version326->update();
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
