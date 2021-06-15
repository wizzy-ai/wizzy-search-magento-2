<?php

namespace Wizzy\Search\Helpers;

use Magento\Catalog\Model\Product\Gallery\ReadHandler;

class GalleryReadHandler extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $galleryReadHandler;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ReadHandler $galleryReadHandler
    ) {
        $this->galleryReadHandler = $galleryReadHandler;
        parent::__construct($context);
    }

    /** Add image gallery to $product */
    public function addGallery($product)
    {
        $this->galleryReadHandler->execute($product);
    }
}
