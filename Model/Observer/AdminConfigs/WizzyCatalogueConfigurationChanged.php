<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\Config\WizzyCatalogueConfiguration;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyCatalogueConfigurationChanged implements ObserverInterface
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
        $this->messageManager = $flashMessagesManager;
        $this->configManager = $configManager;
        $this->queueManager = $queueManager;
        $this->storeManager = $storeManager;
        $this->wizzyCatalogueConfiguration = $wizzyCatalogueConfiguration;
        $this->entitiesSync = $entitiesSync;
    }

    public function execute(EventObserver $observer)
    {
        $storeCatalogueConfigurations = $this->request->getParam('groups');
        $affectedStoreId = $this->getValidatedStoreId();

        if ($affectedStoreId === null) {
            return $this;
        }

        $storeCatalogueConfigurations = json_encode($storeCatalogueConfigurations);

        $previousConfigurations = $this->configManager->getCustomStoreConfig(
            ConfigManager::CATALOGUE_CONFIG,
            $affectedStoreId
        );

        if ($storeCatalogueConfigurations != $previousConfigurations &&
           $this->entitiesSync->hasAnyEntitiesAddedInSync(
               $affectedStoreId,
               EntitiesSync::ENTITY_TYPE_PRODUCT
           )
        ) {
            $this->messageManager->warning(
                'Catalogue configuration has been updated, 
                Catalogue data has been added for sync again. 
                Please execute the Queue Runner Indexer if you want to do it now!'
            );
            $this->wizzyCatalogueConfiguration->clearProductIndexingJobs($affectedStoreId);
            $this->queueManager->enqueue(CatalogueReindexer::class, $affectedStoreId, [$affectedStoreId]);
        }

        $this->configManager->save(
            ConfigManager::CATALOGUE_CONFIG,
            $storeCatalogueConfigurations,
            ScopeInterface::SCOPE_STORES,
            $affectedStoreId
        );
        return $this;
    }

    private function getValidatedStoreId()
    {
        $storeId = $this->request->getParam('store');

        if ($storeId === null || is_array($storeId)) {
            return null;
        }

        $storeId = trim((string) $storeId);

        if ($storeId === '' || !ctype_digit($storeId)) {
            return null;
        }

        $storeId = (int) $storeId;

        if ($storeId <= 0) {
            return null;
        }

        try {
            return (int) $this->storeManager->getStoreById($storeId)->getId();
        } catch (NoSuchEntityException $exception) {
            return null;
        }
    }
}
