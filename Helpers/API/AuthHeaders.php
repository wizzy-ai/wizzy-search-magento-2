<?php

namespace Wizzy\Search\Helpers\API;

class AuthHeaders {
  public static function headers(string $storeId, string $storeAPIKey, string $storeSecret = NULL) {
    $headers = [
      'x-api-key'  => $storeAPIKey,
      'x-store-id' => $storeId,
    ];

    if (!empty($storeSecret)) {
      $headers['x-store-secret'] = $storeSecret;
    }

    return $headers;
  }

  public static function headersFromArray(array $credentials, $isForAdminOp = FALSE) {
    $headers = [
      'x-api-key'  => $credentials['apiKey'],
      'x-store-id' => $credentials['storeId'],
    ];

    if ($isForAdminOp) {
      $headers['x-store-secret'] = $credentials['storeSecret'];
    }

    return $headers;
  }
}