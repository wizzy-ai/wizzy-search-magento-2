<?php

namespace Wizzy\Search\Services\Setup;

class Version310
{
    private $setupUtils;

    public function __construct(SetupUtils $setupUtils)
    {
        $this->setupUtils = $setupUtils;
    }

    private $defaultConfigs = [
        "wizzy_autocomplete_configuration/autocomplete_advanced_configuration/show_default_suggestions" => 0,
        "wizzy_autocomplete_configuration/autocomplete_advanced_configuration/show_default_products" => 0,
        "wizzy_autocomplete_configuration/autocomplete_advanced_configuration/display_recently_searched_terms" => 0,
        "wizzy_autocomplete_configuration/autocomplete_advanced_configuration/
        autocomplete_recently_viewed_products" => 0,
        "wizzy_autocomplete_configuration/autocomplete_advanced_configuration/autocomplete_pinned_products" => 0
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
