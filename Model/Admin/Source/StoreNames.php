<?php

namespace Wizzy\Search\Model\Admin\Source;

use Magento\Store\Model\StoreManagerInterface;
use Wizzy\Search\Services\Store\StoreManager;

class StoreNames
{
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        StoreManager $_storeManager
    ) {
        $this->storeManager = $storeManager;
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
