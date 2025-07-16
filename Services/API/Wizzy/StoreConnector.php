<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Helpers\API\AuthHeaders;

class StoreConnector
{
    private $wizzyAPIConnector;
    private $authHeaders;
    private $wizzyAPIEndpoints;

    public function __construct(
        WizzyAPIConnector $wizzyAPIConnector,
        AuthHeaders $authHeaders,
        WizzyAPIEndPoints $wizzyAPIEndpoints
    ) {
        $this->wizzyAPIConnector = $wizzyAPIConnector;
        $this->authHeaders = $authHeaders;
        $this->wizzyAPIEndpoints = $wizzyAPIEndpoints;
    }

    public function auth(string $storeId, string $storeApiKey, string  $storeSecret)
    {
        $response = $this->wizzyAPIConnector->send(
            $this->wizzyAPIEndpoints->getStoreAuthEndpoint(),
            'post',
            [],
            $this->authHeaders->get($storeId, $storeApiKey, $storeSecret)
        );
        return $response->getStatus();
    }
}
