<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;

class CurrencyRate
{

    private $wizzyAPIWrapper;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
    }

    public function save($currencyRates, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveCurrencyRates($currencyRates, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
           // Log the error.
            return false;
        }
    }
}
