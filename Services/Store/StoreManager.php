<?php

namespace Wizzy\Search\Services\Store;

use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

class StoreManager
{

    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Get specific store by store ID
     * @param $storeId
     * @return StoreInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreById($storeId) : ?StoreInterface
    {
        return $this->storeManager->getStore($storeId);
    }

    public function getCurrentStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    public function getCurrentStoreCurrency()
    {
        return $this->storeManager->getStore()->getCurrentCurrency();
    }

    public function getToSyncStoreIds($storeId = '')
    {
        $storeIds = [];
        if ($storeId != "" && $storeId != "0") {
            $storeIds [] = $storeId;
        } else {
            $storeIds = $this->getActivateWizzyStores();
        }

        return $storeIds;
    }

    public function getCredentials($storeId)
    {
        foreach ($this->storeManager->getStores() as $store) {
            if ($store->getId() == $storeId) {
                $storeConfigs = $store->getConfig('wizzy_store_credentials/store_credentials');
                $storeId = trim($storeConfigs['store_id']);
                $storeSecret = trim($storeConfigs['store_secret']);
                $apiKey = trim($storeConfigs['api_key']);

                return [
                 'storeId' => $storeId,
                 'storeSecret' => $storeSecret,
                 'apiKey' => $apiKey,
                ];
            }
        }

        return null;
    }

    public function getActivateWizzyStores()
    {
        $storeIds = [];

        foreach ($this->storeManager->getStores() as $store) {
            $storeConfigs = $store->getConfig('wizzy_store_credentials/store_credentials');
            if ($storeConfigs !== null && is_array($storeConfigs)) {
                $storeId = trim($storeConfigs['store_id']);
                $storeSecret = trim($storeConfigs['store_secret']);
                $apiKey = trim($storeConfigs['api_key']);

                if (!empty($storeId) && !empty($storeSecret) && !empty($apiKey)) {
                    $storeIds[] = $store->getId();
                }
            }
        }

        return $storeIds;
    }
    public function getActivateWizzyStoreNames()
    {
        $storeNames = [];

        foreach ($this->storeManager->getStores() as $store) {
            $storeConfigs = $store->getConfig('wizzy_store_credentials/store_credentials');
            if ($storeConfigs !== null && is_array($storeConfigs)) {
                $storeId = trim($storeConfigs['store_id']);
                $storeSecret = trim($storeConfigs['store_secret']);
                $apiKey = trim($storeConfigs['api_key']);

                if (!empty($storeId) && !empty($storeSecret) && !empty($apiKey)) {
                    $storeNames[] = $store->getName();
                }
            }
        }

        return $storeNames;
    }
    public function getActiveWizzyWebsites()
    {
        $websiteIds = [];

        foreach ($this->storeManager->getStores() as $store) {
            $websiteId = $store->getWebsiteId();
            $websiteIds[$websiteId] = true;
        }

        return array_keys($websiteIds);
    }

    public function getAllStores()
    {
        $storeIds = [];

        foreach ($this->storeManager->getStores() as $store) {
            $storeIds[] = $store->getId();
        }

        return $storeIds;
    }

    public function getAllStoreBaseUrls()
    {
        $baseUrls = [];
        $storeIds = $this->getActivateWizzyStores();

        foreach ($storeIds as $storeId) {
            $store = $this->storeManager->getStore($storeId);
            $storeId = $store->getId();
            $baseUrl = $store->getBaseUrl();

            $baseUrls[] = [
                'store_id' => $storeId,
                'base_url' => $baseUrl,
            ];
        }

        return $baseUrls;
    }
    
    public function getCurrentStoreBaseUrl()
    {
        return $currentStore = [
            'store_id' => $this->storeManager->getStore()->getId(),
            'base_url' => $this->storeManager->getStore()->getBaseUrl(),
        ];
    }
}
