<?php

namespace Wizzy\Search\Services\Store;

class StoreTaxConfig
{
    private $configManager;

    const TAX_CALCULATION_CATALOG_PRICES = "tax/calculation/price_includes_tax";
    const TAX_CATALOG_PRICES_DISPLAY_TYPE = 'tax/display/type';

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function isCatalogPriceIncludeTax()
    {
        return ($this->configManager->getStoreConfig(self::TAX_CALCULATION_CATALOG_PRICES, $this->storeId) === 1);
    }

    public function getTaxCatalogPricesDisplayType()
    {
        return $this->configManager->getStoreConfig(self::TAX_CATALOG_PRICES_DISPLAY_TYPE, $this->storeId);
    }
}
