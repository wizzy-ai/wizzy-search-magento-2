<?php

namespace Wizzy\Search\Services\Store;

class StoreAdvancedConfig
{
    private $configManager;

    const WIZZY_ADVANCED_CONFIGURATION = "wizzy_advanced_configuration";

   // Advanced section configuration
    const WIZZY_ADVANCED_SECTION_CONFIGURATION = self::WIZZY_ADVANCED_CONFIGURATION . "/advanced_configuration";
    const IS_OVERRIDING_EVENTJS = self::WIZZY_ADVANCED_SECTION_CONFIGURATION . "/overriding_eventsjs";
    const TEMPLATE_ATTRIBUTES = self::WIZZY_ADVANCED_SECTION_CONFIGURATION . "/template_attributes";
    const INCLUDE_CUSTOM_CSS = self::WIZZY_ADVANCED_SECTION_CONFIGURATION . "/include_custom_css";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function isOverridingEventsjs()
    {
        return ($this->configManager->getStoreConfig(self::IS_OVERRIDING_EVENTJS, $this->storeId) == 1);
    }

    public function hasToIncludeCustomCss()
    {
        return ($this->configManager->getStoreConfig(self::INCLUDE_CUSTOM_CSS, $this->storeId) == 1);
    }
    
    public function getTemplateAttributes()
    {
        $templateAttributes = $this->configManager->getStoreConfig(self::TEMPLATE_ATTRIBUTES, $this->storeId);
        return (empty($templateAttributes) || $templateAttributes == null) ? [] : explode(",", $templateAttributes);
    }
}
