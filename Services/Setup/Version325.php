<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version325
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_search_configuration/no_results_page_configuration/no_results_page_title"
            => "We couldn't find any matches!",
        "wizzy_search_configuration/no_results_page_configuration/no_results_products_count"
            => 8,
        "wizzy_search_configuration/no_results_page_configuration/no_results_page_sub_title"
            => "Check out Our Bestsellers"
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
