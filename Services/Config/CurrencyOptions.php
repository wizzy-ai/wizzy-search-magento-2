<?php

namespace Wizzy\Search\Services\Config;

use Wizzy\Search\Services\Queue\Processors\UpdateCurrencyOptions;
use Wizzy\Search\Services\Queue\Processors\UpdateCurrencyRates;
use Wizzy\Search\Services\Queue\QueueManager;

class CurrencyOptions {

   private $queueManager;

   public function __construct(QueueManager $queueManager) {
      $this->queueManager = $queueManager;
   }

   public function onOptionsUpdated($storeId = 0) {
      // TODO: Replace StoreID to specific storeID or if the settings is being updated for website or default config then enqueue jobs for all realted stores.

      // StoreID will be zero for now as we will be updating the currency options for all available stores in one call.
      $this->queueManager->clear($storeId, UpdateCurrencyOptions::class);
      $this->queueManager->clear($storeId, UpdateCurrencyRates::class);
      $this->queueManager->enqueue(UpdateCurrencyOptions::class, $storeId);
      $this->queueManager->enqueue(UpdateCurrencyRates::class, 0);
   }
}