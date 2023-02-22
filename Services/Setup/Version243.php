<?php

namespace Wizzy\Search\Services\Setup;

class Version243
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_advanced_configuration/reindex/has_to_add_all_products_in_sync" => 1,
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
