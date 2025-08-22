<?php

namespace Wizzy\Search\Helpers\API;

class AuthHeaders
{
    public function get(string $storeId, string $storeAPIKey, ?string $storeSecret = null)
    {
        $headers = [
        'x-api-key'  => $storeAPIKey,
        'x-store-id' => $storeId,
        ];

        if (!empty($storeSecret)) {
            $headers['x-store-secret'] = $storeSecret;
        }

        return $headers;
    }

    public function getFromArray(array $credentials, $isForAdminOp = false)
    {
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
