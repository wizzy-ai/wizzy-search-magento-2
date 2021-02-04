<?php

namespace Wizzy\Search\Services\Store;

class StoreImageConfig
{
    private $configManager;

    const CATALOG_IMAGE_PLACEHOLDER = "catalog/placeholder/image_placeholder";
    const CATALOG_THUMBNAIL_IMAGE_PLACEHOLDER = "catalog/placeholder/thumbnail_placeholder";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function getImagePlaceholder()
    {
        return $this->configManager->getStoreConfig(self::CATALOG_IMAGE_PLACEHOLDER, $this->storeId);
    }

    public function getThumbnailImagePlaceholder()
    {
        return $this->configManager->getStoreConfig(self::CATALOG_THUMBNAIL_IMAGE_PLACEHOLDER, $this->storeId);
    }
}
