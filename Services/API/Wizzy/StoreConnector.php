<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Helpers\API\AuthHeaders;

class StoreConnector
{
    private $wizzyAPIConnector;
    private $authHeaders;

    public function __construct(WizzyAPIConnector $wizzyAPIConnector, AuthHeaders $authHeaders)
    {
        $this->wizzyAPIConnector = $wizzyAPIConnector;
        $this->authHeaders = $authHeaders;
    }

    public function auth(string $storeId, string $storeApiKey, string  $storeSecret)
    {
        $response = $this->wizzyAPIConnector->send(
            WizzyAPIEndPoints::STORE_AUTH,
            'post',
            [],
            $this->authHeaders->get($storeId, $storeApiKey, $storeSecret)
        );
        return $response->getStatus();
    }
}
