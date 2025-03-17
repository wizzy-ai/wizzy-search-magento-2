<?php

namespace Wizzy\Search\Services\Store;

class StoreSearchFormConfig
{
    private $configManager;

    const WIZZY_SEARCH_FORM_CONFIGURATION = "wizzy_search_form_configuration";

    const WIZZY_SEARCH_INPUT = self::WIZZY_SEARCH_FORM_CONFIGURATION . "/search_input_configuration";
    const WIZZY_SEARCH_INPUT_PLACEHOLDER = self::WIZZY_SEARCH_INPUT . "/search_input_placeholder";

    const WIZZY_SEARCH_ANIMATED_PLACEHOLDER = self::WIZZY_SEARCH_FORM_CONFIGURATION
    . "/animated_placeholder_configuration";

    const IS_ANIMATED_PLACEHOLDER_ENABLED = self::WIZZY_SEARCH_ANIMATED_PLACEHOLDER . "/animated_placeholder_enabled";
    const ANIMATED_PLACEHOLDER_TERMS = self::WIZZY_SEARCH_ANIMATED_PLACEHOLDER . "/animated_placeholder_terms";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function getSearchInputPlaceholder()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_SEARCH_INPUT_PLACEHOLDER, $this->storeId);
    }

    public function hasToEnableAnimatedPlaceholders()
    {
        return ($this->configManager->getStoreConfig(self::IS_ANIMATED_PLACEHOLDER_ENABLED, $this->storeId) == 1);
    }

    public function getAnimatedPlaceholderTerms()
    {
        $animatedPlaceholderTerms = $this->configManager->getStoreConfig(
            self::ANIMATED_PLACEHOLDER_TERMS,
            $this->storeId
        );
        if (!$animatedPlaceholderTerms) {
            return [];
        }

        try {
            $animatedPlaceholderTerms = json_decode($animatedPlaceholderTerms, true);
            $animatedPlaceholderTerms = array_column($animatedPlaceholderTerms, 'key');
        } catch (\Exception $e) {
            return [];
        }
        
        $animatedPlaceholderTermsArray = [];
        
        foreach ($animatedPlaceholderTerms as $animatedPlaceholderTerm) {
            $animatedPlaceholderTermsArray[] = "Search for ".$animatedPlaceholderTerm;
        }

        return $animatedPlaceholderTermsArray;
    }
}
