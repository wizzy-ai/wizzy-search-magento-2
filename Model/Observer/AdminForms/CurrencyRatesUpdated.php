<?php

namespace Wizzy\Search\Model\Observer\AdminForms;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Queue\Processors\UpdateCurrencyRates;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;

class CurrencyRatesUpdated implements ObserverInterface {
   private $request;
   private $messageManager;
   private $queueManager;
   private $storeManager;

   public function __construct(
      RequestInterface $request,
      FlashMessagesManager $flashMessagesManager,
      QueueManager $queueManager,
      StoreManager $storeManager
   ) {
      $this->request = $request;
      $this->messageManager = $flashMessagesManager;
      $this->queueManager = $queueManager;
      $this->storeManager = $storeManager;
   }

   public function execute(EventObserver $observer) {
      // StoreID will always be zero as currency rates same across all stores.

      $this->queueManager->clear(0, UpdateCurrencyRates::class);
      $this->queueManager->enqueue(UpdateCurrencyRates::class, 0);
      return $this;
   }
}