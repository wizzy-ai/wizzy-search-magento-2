<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version135
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        'wizzy_catalogue_configuration/catalogue_configuration_images/thumbnail_image_width' => '240',
        'wizzy_catalogue_configuration/catalogue_configuration_images/thumbnail_image_height' => '300',
        'wizzy_search_configuration/search_results_facets_configuration/left_facets_has_to_collapsible' => '0',
        "wizzy_search_configuration/search_results_facets_configuration/left_facets_default_collapsible_behaviour"
            => 'OPENED',
        "wizzy_search_configuration/search_results_facets_configuration/left_first_facet_default_collapsible_behaviour"
            => 'OPENED',
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
