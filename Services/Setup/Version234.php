<?php

namespace Wizzy\Search\Services\Setup;

class Version234
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_advanced_configuration/sync/products_sync_batch_size" => 2000,
        "wizzy_advanced_configuration/sync/wizzy_dequeue_size" => 7,
        "wizzy_catalogue_configuration/catalogue_configuration_prices/msrp_attribute" => 'msrp'
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
