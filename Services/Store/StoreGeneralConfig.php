<?php

namespace Wizzy\Search\Services\Store;

class StoreGeneralConfig
{
    private $configManager;

    const WIZZY_GENERAL_CONFIGURATION = "wizzy_general_configuration";

   // General section configuration
    const WIZZY_GENERAL_SECTION_CONFIGURATION = self::WIZZY_GENERAL_CONFIGURATION . "/general_configuration";
    const IS_SYNC_ENABLED = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/enable_sync";
    const IS_SEARCH_ENABLED = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/enable_instant_search";
    const IS_AUTOCOMPLETE_ENABLED = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/enable_autocomplete";
    const IS_ANALYTICS_ENABLED = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/enable_analytics";
    const INSTANT_SEARCH_BEHAVIOUR = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/instant_search_behavior";
    const REPLACE_CATEGORY_PAGE = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/replace_category_page";
    const CATEGORY_CLICK_BEHAVIOUR = self::WIZZY_GENERAL_SECTION_CONFIGURATION . "/category_click_behaviour";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function isSyncEnabled()
    {
        return ($this->configManager->getStoreConfig(self::IS_SYNC_ENABLED, $this->storeId) == 1);
    }

    public function getCategoryClickBehaviour()
    {
        return $this->configManager->getStoreConfig(self::CATEGORY_CLICK_BEHAVIOUR, $this->storeId);
    }

    public function isInstantSearchEnabled()
    {
        return ($this->configManager->getStoreConfig(self::IS_SEARCH_ENABLED, $this->storeId) == 1);
    }

    public function hasToReplaceCategoryPage()
    {
        return ($this->configManager->getStoreConfig(self::REPLACE_CATEGORY_PAGE, $this->storeId) == 1);
    }

    public function isAutocompleteEnabled()
    {
        return ($this->configManager->getStoreConfig(self::IS_AUTOCOMPLETE_ENABLED, $this->storeId) == 1);
    }

    public function isAnalyticsEnabled()
    {
        return ($this->configManager->getStoreConfig(self::IS_ANALYTICS_ENABLED, $this->storeId) == 1);
    }

    public function getInstantSearchBehaviour()
    {
        return $this->configManager->getStoreConfig(self::INSTANT_SEARCH_BEHAVIOUR, $this->storeId);
    }
}
