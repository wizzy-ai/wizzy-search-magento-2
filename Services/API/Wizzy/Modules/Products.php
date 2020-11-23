<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Wizzy\Search\Services\Indexer\IndexerOutput;

class Products
{

    private $wizzyAPIWrapper;
    private $output;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper, IndexerOutput $output)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
        $this->output = $output;
    }

    public function save(array $products, $storeId)
    {
        $response = $this->wizzyAPIWrapper->saveProducts($products, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Products Save API failed',
               'Total Produces' => count($products),
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }

    public function delete(array $products, $storeId)
    {
        $response = $this->wizzyAPIWrapper->deleteProducts($products, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Products Delete API failed',
               'Total Produces' => count($products),
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }
}
