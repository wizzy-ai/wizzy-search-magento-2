<?php

namespace Wizzy\Search\Services\Store;

use Magento\Framework\App\Request\Http;
use Wizzy\Search\Services\Store\ConfigManager;

class StoreSyncDebugConfig
{
    private $request;
    private $configManager;
    private $storeId;

    const WIZZY_DEBUG_SYNC = "wizzy_debug_sync";
    const DEBUG_SYNC = self::WIZZY_DEBUG_SYNC . "/debug_sync";
    const PRODUCT_IDS = self::DEBUG_SYNC . "/product_ids";
    const WIZZY_STORES = self::DEBUG_SYNC . "/wizzy_stores";

    public function __construct(
        Http $request,
        ConfigManager $configManager
    ) {
        $this->request = $request;
        $this->configManager = $configManager;
        $this->storeId = $this->request->getParam('store');
    }

    public function getProductIdsTobeDebugged()
    {
        $productIds = $this->configManager->getStoreConfig(
            self::PRODUCT_IDS,
            $this->storeId
        );
        return $productIds;
    }
    
    public function getStoreId()
    {
        $storeId = $this->configManager->getStoreConfig(
            self::WIZZY_STORES,
            $this->storeId
        );
        return $storeId;
    }
}
