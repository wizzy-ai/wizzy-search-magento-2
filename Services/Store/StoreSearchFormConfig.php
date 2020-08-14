<?php

namespace Wizzy\Search\Services\Store;

class StoreSearchFormConfig {
   private $configManager;

   const WIZZY_SEARCH_FORM_CONFIGURATION = "wizzy_search_form_configuration";

   const WIZZY_SEARCH_INPUT = self::WIZZY_SEARCH_FORM_CONFIGURATION . "/search_input_configuration";
   const WIZZY_SEARCH_INPUT_PLACEHOLDER = self::WIZZY_SEARCH_INPUT . "/search_input_placeholder";

   private $storeId;

   public function __construct(ConfigManager $configManager) {
      $this->configManager = $configManager;
   }

   public function setStore(string $storeId) {
      $this->storeId = $storeId;
   }

   public function getSearchInputPlaceholder() {
      return $this->configManager->getStoreConfig(self::WIZZY_SEARCH_INPUT_PLACEHOLDER, $this->storeId);
   }
}