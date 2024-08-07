<?php

namespace Wizzy\Search\Services\Store;

class StoreAdvancedConfig
{
    private $configManager;

    const WIZZY_ADVANCED_CONFIGURATION = "wizzy_advanced_configuration";

    // Advanced section configuration
    const WIZZY_ADVANCED_SECTION_CONFIGURATION = self::WIZZY_ADVANCED_CONFIGURATION . "/advanced_configuration";
    const TEMPLATE_ATTRIBUTES = self::WIZZY_ADVANCED_SECTION_CONFIGURATION . "/template_attributes";
    const INCLUDE_CUSTOM_CSS = self::WIZZY_ADVANCED_SECTION_CONFIGURATION . "/include_custom_css";
    const WIZZY_ADVANCED_SYNC = self::WIZZY_ADVANCED_CONFIGURATION . "/sync";
    const PRODUCTS_SYNC_BATCH_SIZE = self::WIZZY_ADVANCED_SYNC . "/products_sync_batch_size";
    const SYNC_DEQUEUE_SIZE = self::WIZZY_ADVANCED_SYNC . "/sync_dequeue_size";
    const SYNC_DEBUGGING = self::WIZZY_ADVANCED_SYNC . "/sync_debugging";
    const HAS_TO_ADD_PRODUCTS_IN_SYNC_ON_ATTRIBUTE_SAVE = self::WIZZY_ADVANCED_SYNC .
    "/has_to_add_products_in_sync_on_attribute_save";
    const REINDEX = self::WIZZY_ADVANCED_CONFIGURATION . "/reindex";
    const HAS_TO_ADD_ALL_PRODUCTS_IN_SYNC = self::REINDEX . "/has_to_add_all_products_in_sync";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
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

    public function getProductsSyncbatchSize()
    {
        $batchSize = $this->configManager->getStoreConfig(self::PRODUCTS_SYNC_BATCH_SIZE, $this->storeId);
        
        if (!$batchSize) {
            $batchSize = 2000;
            return $batchSize;
        }

        if ($batchSize) {
            $batchSize = (int)$batchSize;
            if ($batchSize > 3500) {
                $batchSize = 3500;
            }
        }
        return $batchSize;
    }

    public function getSyncDequeueSize()
    {
        $syncDequeueSize = $this->configManager->getStoreConfig(self::SYNC_DEQUEUE_SIZE, $this->storeId);
        if (!$syncDequeueSize) {
            $syncDequeueSize = 7;
        }
        return $syncDequeueSize;
    }

    public function hasToEnableSyncDebugging()
    {
        return $this->configManager->getStoreConfig(self::SYNC_DEBUGGING, $this->storeId);
    }

    public function hasToAddProductsInSyncOnAttributeSave()
    {
        return ($this->configManager->getStoreConfig(
            self::HAS_TO_ADD_PRODUCTS_IN_SYNC_ON_ATTRIBUTE_SAVE,
            $this->storeId
        ) == 1);
    }

    public function hasToAddAllProductsInSync($storeId)
    {
        return ($this->configManager->getStoreConfig(self::HAS_TO_ADD_ALL_PRODUCTS_IN_SYNC, $storeId));
    }
}
