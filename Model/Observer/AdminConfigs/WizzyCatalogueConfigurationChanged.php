<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Config\WizzyCatalogueConfiguration;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyCatalogueConfigurationChanged implements ObserverInterface {
   private $request;
   private $messageManager;
   private $configManager;
   private $queueManager;
   private $storeManager;
   private $wizzyCatalogueConfiguration;

   public function __construct(
      RequestInterface $request,
      FlashMessagesManager $flashMessagesManager,
      ConfigManager $configManager,
      QueueManager $queueManager,
      StoreManager $storeManager,
      WizzyCatalogueConfiguration $wizzyCatalogueConfiguration
   ) {
      $this->request = $request;
      $this->messageManager = $flashMessagesManager;
      $this->configManager = $configManager;
      $this->queueManager = $queueManager;
      $this->storeManager = $storeManager;
      $this->wizzyCatalogueConfiguration = $wizzyCatalogueConfiguration;
   }

   public function execute(EventObserver $observer) {
      $storeCatalogueConfigurations = $this->request->getParam('groups');
      $storeCatalogueConfigurations = json_encode($storeCatalogueConfigurations);

      $previousConfigurations = $this->configManager->getCustomStoreConfig(ConfigManager::CATALOGUE_CONFIG, $this->storeManager->getCurrentStoreId());

      if ($storeCatalogueConfigurations != $previousConfigurations) {
         $this->messageManager->warning('Catalogue configuration has been updated, Catalogue data has been added for sync again. Please execute the Queue Runner Indexer if you want to do it now!');
         $this->wizzyCatalogueConfiguration->clearProductIndexingJobs($this->storeManager->getCurrentStoreId());
         $this->queueManager->enqueue(CatalogueReindexer::class, $this->storeManager->getCurrentStoreId(), [

         ]);
      }

      $this->configManager->saveStoreConfig(ConfigManager::CATALOGUE_CONFIG, $storeCatalogueConfigurations);
      return $this;
   }
}