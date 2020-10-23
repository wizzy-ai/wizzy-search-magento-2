<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;

class Currency
{

    private $wizzyAPIWrapper;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
    }

    public function saveDefaultCurrency($defaultCurrency, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveDefaultCurrency($defaultCurrency, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
           // Log the error.
            return $response;
        }
    }

    public function save($currencies, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveCurrencies($currencies, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
           // Log the error.
            return $response;
        }
    }

    public function delete($currencies, $storeId)
    {
        $response = $this->wizzyAPIWrapper->deleteCurrencies($currencies, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
           // Log the error.
            return $response;
        }
    }

    public function get($storeId)
    {
        $response = $this->wizzyAPIWrapper->getCurrencies($storeId);
        if ($response->getStatus()) {
            return $response['payload']['response']['payload']['currencies'];
        } else {
           // Log the error.
            return $response;
        }
    }

    public function saveDisplayCurrency($displayCurrency, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveDisplayCurrency($displayCurrency, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
           // Log the error.
            return $response;
        }
    }
}
