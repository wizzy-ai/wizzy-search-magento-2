<?php

namespace Wizzy\Search\Services\Request;

use Magento\Framework\App\RequestInterface;

class ProductManager
{

    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function getProductId()
    {
        return ($this->isOnProductPage()) ? $this->request->getParam('id') : '';
    }

    public function isOnProductPage()
    {
        return (
         $this->request->getModuleName() === 'catalog' &&
         $this->request->getFullActionName() === 'catalog_product_view'
        );
    }
}
