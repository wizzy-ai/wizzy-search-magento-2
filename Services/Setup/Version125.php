<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version125
{
    private $setup;
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
      'wizzy_catalogue_configuration/catalogue_configuration_brands/is_brand_mandatory_for_sync' => '0',
      'wizzy_catalogue_configuration/catalogue_configuration_prices/use_msrp_as_original_price' => '0',
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
