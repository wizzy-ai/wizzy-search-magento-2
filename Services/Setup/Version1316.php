<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version1316
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_autocomplete_configuration/autocomplete_menu/brands_title" => 'Brands',
    ];

    public function update()
    {
        $this->setDefaults();
    }

    private function setDefaults()
    {
        $this->setDefaultConfig();
    }

    private function setDefaultConfig()
    {
        $this->setupUtils->setDefaultConfig($this->defaultConfigs);
    }
}
