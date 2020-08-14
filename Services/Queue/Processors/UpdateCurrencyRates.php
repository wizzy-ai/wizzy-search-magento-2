<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\API\Wizzy\Modules\CurrencyRate;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class UpdateCurrencyRates extends QueueProcessorBase {

   private $storeManager;
   private $currencyManager;
   private $currencyRateUpdater;
   private $storeGeneralConfig;

   public function __construct(StoreManager $storeManager, StoreGeneralConfig $storeGeneralConfig, CurrencyManager $currencyManager, CurrencyRate $currencyRate) {
      $this->storeManager = $storeManager;
      $this->currencyManager = $currencyManager;
      $this->currencyRateUpdater = $currencyRate;
      $this->storeGeneralConfig = $storeGeneralConfig;
   }

   public function execute(array $data, $storeId) {
      if (!$this->storeGeneralConfig->isSyncEnabled()) {
         return TRUE;
      }
      $storeIds = $this->storeManager->getToSyncStoreIds($storeId);

      foreach ($storeIds as $storeId) {
         $currencyRates = $this->getCurrencyRates($storeId);
         $this->currencyRateUpdater->save($currencyRates, $storeId);
      }

      return TRUE;
   }

   private function getCurrencyRates($storeId) {
      $code = $this->currencyManager->getDefaultCurrency($storeId);
      $currencyRates = $this->currencyManager->getCurrencyRates($storeId, $code);
      $currencyRatesToPush = [];

      foreach ($currencyRates as $targetCode => $currencyRate) {
         if ($currencyRate) { // Skipping the rates which are not set yet.
            $currencyRatesToPush[] = [
               'sourceCode' => $code,
               'targetCode' => $targetCode,
               'rate' => floatval($currencyRate)
            ];
         }
      }

      return $currencyRatesToPush;
   }
}