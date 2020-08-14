<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Helpers\API\AuthHeaders;

class StoreConnector {
  private $wizzyAPIConnector;

  public function __construct(WizzyAPIConnector $wizzyAPIConnector) {
    $this->wizzyAPIConnector = $wizzyAPIConnector;
  }

  public function auth(string $storeId,string $storeApiKey, string  $storeSecret) {
    $response = $this->wizzyAPIConnector->send(WizzyAPIEndPoints::storeAuth(), 'post', [], AuthHeaders::headers($storeId, $storeApiKey, $storeSecret));
    return $response->getStatus();
  }

}