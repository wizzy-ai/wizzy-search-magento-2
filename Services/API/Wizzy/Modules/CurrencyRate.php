<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Wizzy\Search\Services\Indexer\IndexerOutput;

class CurrencyRate
{

    private $wizzyAPIWrapper;
    private $output;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper, IndexerOutput $output)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
        $this->output = $output;
    }

    public function save($currencyRates, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveCurrencyRates($currencyRates, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Current Rates Save API Failed.',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }
}
