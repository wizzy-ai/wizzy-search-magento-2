<?php

namespace Wizzy\Search\Services\Store;

use Magento\Framework\App\Request\Http;
use Wizzy\Search\Services\Store\ConfigManager;

class StoreCopyConfig
{
    private $request;
    private $configManager;
    private $storeId;

    const WIZZY_COPY_CONFIGURATION = "wizzy_copy_configuration";
    const COPY_CONFIGURATION = self::WIZZY_COPY_CONFIGURATION . "/copy_configuration";
    const COPY_CONFIGURATION_FROM = self::COPY_CONFIGURATION . "/from_store";

    public function __construct(
        Http $request,
        ConfigManager $configManager
    ) {
        $this->request = $request;
        $this->configManager = $configManager;
        $this->storeId = $this->request->getParam('store');
    }

    public function getCopyConfigFrom()
    {
        $copyConfigFrom = $this->configManager->getStoreConfig(self::COPY_CONFIGURATION_FROM, $this->storeId);
        return $copyConfigFrom;
    }
}
