<?php

namespace Wizzy\Search\Services\Store;

class StoreCredentialsConfig
{
    private $configManager;

    const WIZZY_CREDENTIALS_CONFIGURATION = "wizzy_store_credentials";

    const WIZZY_STORE_CREDENTIALS = self::WIZZY_CREDENTIALS_CONFIGURATION . "/store_credentials";
    const WIZZY_STORE_ID = self::WIZZY_STORE_CREDENTIALS . "/store_id";
    const WIZZY_STORE_SECRET = self::WIZZY_STORE_CREDENTIALS . "/store_secret";
    const WIZZY_STORE_API_KEY = self::WIZZY_STORE_CREDENTIALS . "/api_key";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function getStoreId()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_STORE_ID, $this->storeId);
    }

    public function getStoreSecret()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_STORE_SECRET, $this->storeId);
    }

    public function getApiKey()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_STORE_API_KEY, $this->storeId);
    }
}
