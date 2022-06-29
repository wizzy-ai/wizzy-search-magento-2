<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

class Version1317
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_autocomplete_configuration/autocomplete_pages/sync_pages" => 0,
        "wizzy_catalogue_configuration/catalogue_configuration_images/replace_child_with_main_image" => 0,
        "wizzy_catalogue_configuration/catalogue_configuration_name/replace_child_name_with_parent" => 1,
        "wizzy_search_configuration/search_results_pagination_configuration/infinite_scroll_offset_desktop" =>
        600,
        "wizzy_search_configuration/search_results_pagination_configuration/infinite_scroll_offset_mobile" =>
        700,
        "wizzy_catalogue_configuration/catalogue_configuration_images/hover_image_type" => 'small',
        "wizzy_catalogue_configuration/catalogue_configuration_images/thumbnail_image_type" => 'thumbnail',
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
