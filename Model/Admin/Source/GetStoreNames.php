<?php

namespace Wizzy\Search\Model\Admin\Source;

use Magento\Store\Model\StoreManagerInterface;

class GetStoreNames
{
    private $storeManager;
 
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function toOptionArray()
    {
        $storeNames = [
            [
                "value" => '',
                "label" => 'select'
            ]
        ];

        foreach ($this->storeManager->getStores() as $store) {
            $storeConfigs = $store->getConfig('wizzy_store_credentials/store_credentials');
            if ($storeConfigs !== null && is_array($storeConfigs)) {
                $storeId = trim($storeConfigs['store_id']);
                $storeSecret = trim($storeConfigs['store_secret']);
                $apiKey = trim($storeConfigs['api_key']);

                if (!empty($storeId) && !empty($storeSecret) && !empty($apiKey)) {
                    $storeNames[] = [
                        "value" => $store->getId(),
                        "label" =>  $store->getName()
                    ];
                    
                }
            }
        }

        return $storeNames;
    }
}
