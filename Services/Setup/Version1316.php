<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version1316
{
    private $setupUtils;
    private $setup;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_autocomplete_configuration/autocomplete_menu/brands_title" => 'Brands',
    ];

    public function update(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
        $this->setDefaults();
    }

    public function install(SchemaSetupInterface $setup)
    {
        $this->setup = $setup;
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