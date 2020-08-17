<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Config\WizzyCatalogueConfiguration;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\Processors\IndexPagesProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyAutocompleteConfigurationChanged implements ObserverInterface
{
    private $request;
    private $messageManager;
    private $configManager;
    private $queueManager;
    private $storeManager;
    private $wizzyCatalogueConfiguration;
    private $entitiesSync;

    public function __construct(
        RequestInterface $request,
        FlashMessagesManager $flashMessagesManager,
        ConfigManager $configManager,
        QueueManager $queueManager,
        StoreManager $storeManager,
        EntitiesSync $entitiesSync,
        WizzyCatalogueConfiguration $wizzyCatalogueConfiguration
    ) {
        $this->request = $request;
        $this->wizzyCatalogueConfiguration = $wizzyCatalogueConfiguration;
        $this->messageManager = $flashMessagesManager;
        $this->configManager = $configManager;
        $this->queueManager = $queueManager;
        $this->storeManager = $storeManager;
        $this->entitiesSync = $entitiesSync;
    }

    public function execute(EventObserver $observer)
    {
        $storeAutocompleteConfigurations = $this->request->getParam('groups');
        $this->reindexCatalogIfRequired($storeAutocompleteConfigurations);
        $this->reindexPagesIfRequired($storeAutocompleteConfigurations);
        return $this;
    }

    private function reindexPagesIfRequired($storeAutocompleteConfigurations)
    {
        $excludedPages = (isset($storeAutocompleteConfigurations['autocomplete_pages']['fields']['exclude_pages'])) ?
           $storeAutocompleteConfigurations['autocomplete_pages']['fields']['exclude_pages'] : [];
        $storeAutocompleteConfigurations = json_encode($excludedPages);

        $previousConfigurations = $this->configManager->getCustomStoreConfig(
            ConfigManager::PAGES_EXCLUDE_CONFIG,
            $this->storeManager->getCurrentStoreId()
        );
        if ($storeAutocompleteConfigurations != $previousConfigurations) {
            $this->messageManager->warning(
                'Pages configuration has been updated, Pages data has been added for sync again. 
                Please execute the Queue Runner Indexer if you want to do it now!'
            );
            $this->queueManager->clear(0, IndexPagesProcessor::class);
            $this->queueManager->enqueue(IndexPagesProcessor::class, 0, [

            ]);
        }

        $this->configManager->saveStoreConfig(ConfigManager::PAGES_EXCLUDE_CONFIG, $storeAutocompleteConfigurations);
    }

    private function reindexCatalogIfRequired($storeAutocompleteConfigurations)
    {
        $autocompleteAttributes = (isset($storeAutocompleteConfigurations['autocomplete_attributes_configuration'])) ?
          $storeAutocompleteConfigurations['autocomplete_attributes_configuration'] : [];
        $storeAutocompleteConfigurations = json_encode($autocompleteAttributes);

        $previousConfigurations = $this->configManager->getCustomStoreConfig(
            ConfigManager::AUTOCOMPLETE_ATTRIBUTES_CONFIG,
            $this->storeManager->getCurrentStoreId()
        );

        if ($storeAutocompleteConfigurations != $previousConfigurations &&
           $this->entitiesSync->hasAnyEntitiesAddedInSync(
               $this->storeManager->getCurrentStoreId(),
               EntitiesSync::ENTITY_TYPE_PRODUCT
           )
        ) {
            $this->messageManager->warning(
                'Autocomplete attributes configuration has been updated, 
                Catalogue data has been added for sync again. 
                Please execute the Queue Runner Indexer if you want to do it now!'
            );
            $this->wizzyCatalogueConfiguration->clearProductIndexingJobs($this->storeManager->getCurrentStoreId());
            $this->queueManager->enqueue(CatalogueReindexer::class, $this->storeManager->getCurrentStoreId(), [

            ]);
        }

        $this->configManager->saveStoreConfig(
            ConfigManager::AUTOCOMPLETE_ATTRIBUTES_CONFIG,
            $storeAutocompleteConfigurations
        );
    }
}
