<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Wizzy\Search\Model\Admin\Source\CategoryClickBehaviours;

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
      'wizzy_general_configuration/general_configuration/category_click_behaviour' =>
         CategoryClickBehaviours::HIT_SEARCH_WITH_CATEGORY_KEYWORD,
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
