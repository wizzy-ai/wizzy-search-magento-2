<?php

namespace Wizzy\Search\Services\Catalogue;

use Wizzy\Search\Services\Store\StoreCatalogueConfig;
use Wizzy\Search\Services\Store\StoreImageConfig;
use Wizzy\Search\Services\Store\StoreManager;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Helper\Image;

class ProductImageManager
{
    private $storeImageConfig;
    private $placeholderImage;
    private $storeManager;
    private $imageHelper;
    private $catalogConfig;

    private $thumbWidth;
    private $thumbHeight;

    public function __construct(
        StoreImageConfig $storeImageConfig,
        StoreManager $storeManager,
        Image $imageHelper,
        StoreCatalogueConfig $storeCatalogueConfig
    ) {
        $this->storeImageConfig = $storeImageConfig;
        $this->placeholderImage = false;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->catalogConfig = $storeCatalogueConfig;

        $this->thumbHeight = 0;
        $this->thumbWidth = 0;
    }

    public function getThumbnail($product, $imageFile)
    {
        $this->setThumbnailSize();
        return $this->imageHelper->init($product, 'category_page_grid')->setImageFile($imageFile)
            ->keepAspectRatio(true)->resize($this->thumbWidth, $this->thumbHeight)->getUrl();
    }

    private function setThumbnailSize()
    {
        if ($this->thumbHeight === 0) {
            $this->thumbHeight = $this->catalogConfig->getThumbnailHeight();
            $this->thumbWidth = $this->catalogConfig->getThumbnailWidth();
        }
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
