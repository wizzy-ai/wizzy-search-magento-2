<?php

namespace Wizzy\Search\Helpers\API;

use Wizzy\Search\Model\API\Response;

class ResponseBuilder {
  public function error($message, $payload = []): Response {
    $response = new Response();
    $response->setStatus(FALSE)
      ->setMessage($message)
      ->setPayload($payload);
    return $response;
  }

  public function success($message, $payload = []): Response {
    $response = new Response();
    $response->setStatus(TRUE)
      ->setMessage($message)
      ->setPayload($payload);

    return $response;
  }
}