<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Wizzy\Search\Services\Indexer\IndexerOutput;

class Currency
{

    private $wizzyAPIWrapper;
    private $output;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper, IndexerOutput $output)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
        $this->output = $output;
    }

    public function saveDefaultCurrency($defaultCurrency, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveDefaultCurrency($defaultCurrency, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Set Default Currency API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }

    public function save($currencies, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveCurrencies($currencies, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Currencies Save API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }

    public function delete($currencies, $storeId)
    {
        $response = $this->wizzyAPIWrapper->deleteCurrencies($currencies, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Currencies Delete API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }

    public function get($storeId)
    {
        $response = $this->wizzyAPIWrapper->getCurrencies($storeId);
        if ($response->getStatus()) {
            return [
               'status' => true,
               'data'   => $response['payload']['response']['payload']['currencies'],
            ];
        } else {
            $this->output->log([
               'Message' => 'Currencies Get API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return [
               'status' => false,
               'data' => $response,
            ];
        }
    }

    public function saveDisplayCurrency($displayCurrency, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveDisplayCurrency($displayCurrency, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Set Display Currency API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }
}
