<?php

namespace Wizzy\Search\Services\API\Wizzy\Modules;

use Wizzy\Search\Services\API\Wizzy\WizzyAPIWrapper;
use Wizzy\Search\Services\Indexer\IndexerOutput;

class Pages
{

    private $wizzyAPIWrapper;
    private $output;

    public function __construct(WizzyAPIWrapper $wizzyAPIWrapper, IndexerOutput $output)
    {
        $this->wizzyAPIWrapper = $wizzyAPIWrapper;
        $this->output = $output;
    }

    public function save($pages, $storeId)
    {
        $response = $this->wizzyAPIWrapper->savePages($pages, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
               'Message' => 'Pages Save API Failed',
               'Response' => json_encode($response->getPayload()),
            ]);
            return $response;
        }
    }

    public function get($storeId)
    {
        $response = $this->wizzyAPIWrapper->getPages($storeId);
        if ($response->getStatus()) {
            $response = $response->getPayload();

            return [
               'status' => true,
               'data'   => $response['response']['payload']['pages'],
            ];
        } else {
            $this->output->log([
              'Message' => 'Pages Get API Failed',
              'Response' => json_encode($response->getPayload()),
            ]);

            return [
               'status' => false,
               'data'   => $response,
            ];
        }
    }

    public function delete($pages, $storeId)
    {
        $response = $this->wizzyAPIWrapper->deletePages($pages, $storeId);
        if ($response->getStatus()) {
            return true;
        } else {
            $this->output->log([
              'Message' => 'Pages Delete API Failed',
              'Response' => json_encode($response->getPayload()),
            ]);

            return $response;
        }
    }
}
