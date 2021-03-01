<?php

namespace Wizzy\Search\Services\Store;

class StoreStockConfig
{
    private $configManager;

    const SHOW_OUT_OF_STOCK_PRODUCTS = "cataloginventory/options/show_out_of_stock";
    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function hasToIncludeOutOfStockProducts()
    {
        return ($this->configManager->getStoreConfig(self::SHOW_OUT_OF_STOCK_PRODUCTS, $this->storeId) == 1);
    }
}
