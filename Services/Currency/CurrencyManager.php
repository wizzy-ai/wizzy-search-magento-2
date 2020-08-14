<?php

namespace Wizzy\Search\Services\Currency;

use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreManager;

class CurrencyManager {
   const CURRENCY_CONFIGURATION = "currency/options";

   const DEFAULT_CURRENCY_CONFIGURATION = self::CURRENCY_CONFIGURATION . "/base";
   const DISPLAY_CURRENCY_CONFIGURATION = self::CURRENCY_CONFIGURATION . "/default";
   const SUPPORTED_CURRENCY_CONFIGURATION = self::CURRENCY_CONFIGURATION . "/allow";

   private $configManager;
   private $currencyInterface;
   private $currencyFactory;

   public function __construct(ConfigManager $configManager, CurrencyInterface $currencyInterface, CurrencyFactory $currencyFactory) {
      $this->configManager = $configManager;
      $this->currencyInterface = $currencyInterface;
      $this->currencyFactory = $currencyFactory;
   }

   public function getDefaultCurrency($storeId) {
      return $this->configManager->getStoreConfig(self::DEFAULT_CURRENCY_CONFIGURATION, $storeId);
   }

   public function getDisplayCurrency($storeId) {
      return $this->configManager->getStoreConfig(self::DISPLAY_CURRENCY_CONFIGURATION, $storeId);
   }

   public function getSupportedCurrencies($storeId) {
      return explode(",", $this->configManager->getStoreConfig(self::SUPPORTED_CURRENCY_CONFIGURATION, $storeId));
   }

   public function getCurrencyByCode($code) {
      return $this->currencyInterface->getCurrency($code);
   }

   public function getCurrencyRates($storeId, $code) {
      $toCurrencies = $this->getSupportedCurrencies($storeId);
      return $this->currencyFactory->create()->load($code)->getCurrencyRates($code, $toCurrencies);
   }
}