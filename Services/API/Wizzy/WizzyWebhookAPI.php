<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\AuthHeaders;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Wizzy\Search\Helpers\API\ResponseBuilder;

class WizzyWebhookAPI
{
    private $responseBuilder;
    private $authHeaders;
    public function __construct(ResponseBuilder $responseBuilder, AuthHeaders $authHeaders)
    {
        $this->responseBuilder = $responseBuilder;
        $this->authHeaders = $authHeaders;
    }

    const TOPIC_BEFORE_PRODUCTS_SYNC = "before_products_sync";
    const TOPIC_BEFORE_PRODUCTS_DELETE = "before_prducts_delete";

    public function broadcast(StoreAdvancedConfig $storeAdvancedConfig, $credentials, $topic, array $data)
    {
        $webhookURLs = $storeAdvancedConfig->getWebhookURLs();

        if ($webhookURLs) {
            $webhookURLs = preg_split('/\r\n|\r|\n/', $webhookURLs);
            foreach ($webhookURLs as $webhookURL) {
                $response = $this->individualBroadcast($credentials, $webhookURL, $topic, $data);
                
                if ($response !== false) {
                    $data = $response;
                }
            }
            return $data;
        }
        return $data;
    }

    public function individualBroadcast($credentials, $webhookUrl, $topic, array $data)
    {
        $client = new Client();
        $headers = $this->authHeaders->getFromArray($credentials, true);
        $headers['Content-Type'] = 'application/json';

        $body = [
            'topic' => $topic,
            'data'  => $data,
        ];

        $payload = [
            'body' => json_encode($body),
            'headers' => $headers,
        ];
        try {
            $response = $client->post($webhookUrl, $payload);
        } catch (Exception $e) {
            return $this->responseBuilder->error('Error occured while connecting to webhook Server', [
                'exeception' => $e->getMessage(),
            ]);
        }
        return json_decode($response->getBody()->getContents(), true);
    }
}
