<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Wizzy\Search\Services\Session\UserSessionManager;
use Magento\Framework\App\Helper\Context;

class Analytics
{
    private $wizzyAPIWrapper;

    const TYPES = [
      UserSessionManager::ADDED_TO_CART => 'collectConverted',
      UserSessionManager::ADDED_TO_WISHLIST => 'collectConverted',
      UserSessionManager::PRODUCTS_PURCHASED => 'collectConverted'
    ];

    const NAMES = [
      UserSessionManager::ADDED_TO_CART => 'Product Added to Cart',
      UserSessionManager::ADDED_TO_WISHLIST => 'Product Added to Wishlist',
      UserSessionManager::PRODUCTS_PURCHASED => 'Product Purchased',
      UserSessionManager::SEARCH_RESULTS_CLICKED => 'Search Results Clicked',
    ];

    private $context;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper, Context $context)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
        $this->context = $context;
    }

    private function getModifiedHeaders(array $headers)
    {
        $headers['X-Forwarded-For']  = $this->context->getRemoteAddress()->getRemoteAddress();
        return $headers;
    }

    public function collectClick(array $clickData, $storeId, array $headers)
    {
        $response = $this->wizzyAPIWrapper->collectClick($clickData, $storeId, $this->getModifiedHeaders($headers));
        if ($response->getStatus()) {
            return true;
        } else {
            return $response;
        }
    }

    public function collectView(array $viewData, $storeId, array $headers)
    {
        $response = $this->wizzyAPIWrapper->collectView($viewData, $storeId, $this->getModifiedHeaders($headers));
        if ($response->getStatus()) {
            return true;
        } else {
            return $response;
        }
    }

    public function collectConverted(array $data, $storeId, array $headers)
    {
        $response = $this->wizzyAPIWrapper->collectConverted($data, $storeId, $this->getModifiedHeaders($headers));
        if ($response->getStatus()) {
            return true;
        } else {
            return $response;
        }
    }
}
