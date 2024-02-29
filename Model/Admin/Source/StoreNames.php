<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Store\StoreManager;

class StoreNames
{
    private $_storeManager;

    public function __construct(StoreManager $_storeManager)
    {
        $this->_storeManager = $_storeManager;
    }

    public function toOptionArray()
    {
        $stores = [
            [
                "value" => '',
                "label" => 'Select'
            ]
        ];
        $storeNames = $this->_storeManager->getActivateWizzyStoreNames();
        $storeIds = $this->_storeManager->getActivateWizzyStores();
        
        foreach ($storeIds as $key => $value) {
            $stores[] = [
                "value" => $value,
                "label" => $storeNames[$key]
            ];
        }

        return $stores;
    }
}
