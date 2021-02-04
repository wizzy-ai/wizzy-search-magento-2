<?php

namespace Wizzy\Search\Services\Catalogue;

use Wizzy\Search\Services\Store\StoreImageConfig;
use Wizzy\Search\Services\Store\StoreManager;
use Magento\Framework\UrlInterface;

class ProductImageManager
{
    private $storeImageConfig;
    private $placeholderImage;
    private $storeManager;

    public function __construct(StoreImageConfig $storeImageConfig, StoreManager $storeManager)
    {
        $this->storeImageConfig = $storeImageConfig;
        $this->placeholderImage = false;
        $this->storeManager = $storeManager;
    }

    public function getPlaceholderImage($storeId)
    {
        if ($this->placeholderImage !== false) {
            return $this->placeholderImage;
        }
        $this->storeImageConfig->setStore($storeId);

        $this->placeholderImage = $this->storeImageConfig->getImagePlaceholder();
        if (!$this->placeholderImage) {
            $this->placeholderImage = $this->storeImageConfig->getThumbnailImagePlaceholder();
        }

        if ($this->placeholderImage === null) {
            $this->placeholderImage = '';
        } else {
            $this->placeholderImage = $this->getAbsoluteImagePath($storeId, $this->placeholderImage);
        }

        return $this->placeholderImage;
    }

    private function getAbsoluteImagePath($storeId, $placeholderImage)
    {
        $URL = $this->storeManager->getStoreById($storeId)->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
        return $URL . 'catalog/product/placeholder/' . $placeholderImage;
    }
}
