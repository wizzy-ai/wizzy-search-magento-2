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
    const DEVELOPER = self::WIZZY_ADVANCED_CONFIGURATION . "/developer";
    const WEBHOOK_URL = self::DEVELOPER . "/webhook_url";
    const CRON_SCHEDULES = self::WIZZY_ADVANCED_CONFIGURATION . "/cron_schedules";
    const ENABLE_SYNC_QUEUE_RUNNER = self::CRON_SCHEDULES . "/enable_sync_queue_runner";
    const ENABLE_INVALIDATE_UNRESPONSIVE_SYNC = self::CRON_SCHEDULES . "/enable_invalidate_unresponsive_sync";
    const ENABLE_PRODUCTS_PRICES_INDEXER = self::CRON_SCHEDULES . "/enable_products_prices_indexer";
    const ENABLE_RECOVER_STALE_ENTITIES = self::CRON_SCHEDULES . "/enable_recover_stale_entities";

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
    public function getWebhookURLs()
    {
        return  $this->configManager->getStoreConfig(self::WEBHOOK_URL, $this->storeId);
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

    public function hasToEnableSyncQueueRunner()
    {
        return ($this->configManager->getDefaultConfig(self::ENABLE_SYNC_QUEUE_RUNNER) == 1);
    }

    public function hasToEnableInvalidateUnresponsiveSync()
    {
        return ($this->configManager->getDefaultConfig(self::ENABLE_INVALIDATE_UNRESPONSIVE_SYNC) == 1);
    }

    public function hasToEnableProductsPricesIndexer()
    {
        return ($this->configManager->getDefaultConfig(self::ENABLE_PRODUCTS_PRICES_INDEXER) == 1);
    }

    public function hasToEnableRecoverStaleEntities()
    {
        return ($this->configManager->getDefaultConfig(self::ENABLE_RECOVER_STALE_ENTITIES) == 1);
    }
}
