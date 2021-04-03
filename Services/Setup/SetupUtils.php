<?php

namespace Wizzy\Search\Services\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Wizzy\Search\Services\Store\StoreManager;

class SetupUtils
{

    private $storeManager;
    private $config;
    private $productMetadata;

    public function __construct(
        StoreManager $storeManager,
        ConfigInterface $config,
        ProductMetadataInterface $productMetadata
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->productMetadata = $productMetadata;
    }

    public function setDefaultConfig($configs)
    {
        $allStores = $this->storeManager->getAllStores();

        foreach ($allStores as $storeId) {
            foreach ($configs as $path => $value) {
                $this->config->saveConfig($path, $this->getConfigValue($value), 'stores', $storeId);
            }
        }
    }

    private function getConfigValue($value)
    {
        $serializeMethod = 'serialize';

        if (is_array($value)) {
            $magentoVersion = $this->productMetadata->getVersion();
            if (version_compare($magentoVersion, '2.2.0-dev', '>=') === true) {
                $serializeMethod = 'json_encode';
            }
            return $serializeMethod($value);
        }

        return $value;
    }
}
