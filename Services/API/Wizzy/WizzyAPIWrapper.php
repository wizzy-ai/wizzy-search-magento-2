<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\AuthHeaders;
use Wizzy\Search\Helpers\API\ResponseBuilder;
use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Model\API\Response;
use Wizzy\Search\Services\API\Wizzy\WizzyWebhookAPI;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyAPIWrapper
{
    private $storeManager;
    private $wizzyApiConnector;
    private $responseBuilder;
    private $authHeaders;
    private $wizzyWebhookAPI;
    private $storeAdvancedConfig;
    private $wizzyAPIEndpoints;

    public function __construct(
        StoreManager $storeManager,
        WizzyAPIConnector $wizzyApiConnector,
        ResponseBuilder $responseBuilder,
        AuthHeaders $authHeaders,
        WizzyWebhookAPI $wizzyWebhookAPI,
        StoreAdvancedConfig $storeAdvancedConfig,
        WizzyAPIEndPoints $wizzyAPIEndpoints
    ) {
        $this->storeManager = $storeManager;
        $this->wizzyApiConnector = $wizzyApiConnector;

        $this->responseBuilder = $responseBuilder;
        $this->authHeaders = $authHeaders;
        $this->wizzyWebhookAPI = $wizzyWebhookAPI;
        $this->storeAdvancedConfig = $storeAdvancedConfig;
        $this->wizzyAPIEndpoints = $wizzyAPIEndpoints;
    }

    public function collectClick(array $clickData, $storeId, array $headers): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getCollectClickEventEndpoint(),
            'POST',
            $clickData,
            array_merge($headers, $this->authHeaders->getFromArray($credentials, true)),
            true
        );
    }

    public function collectView(array $viewData, $storeId, array $headers): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getCollectViewEventEndpoint(),
            'POST',
            $viewData,
            array_merge($headers, $this->authHeaders->getFromArray($credentials, true)),
            true
        );
    }

    public function collectConverted(array $data, $storeId, array $headers): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getCollectConvertedEventEndpoint(),
            'POST',
            $data,
            array_merge($headers, $this->authHeaders->getFromArray($credentials, true)),
            true
        );
    }

    public function saveProducts(array $products, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        $mappedProducts = $this->getTransformedProductsFromWebhook($products, $credentials);
        
        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSaveProductsEndpoint(),
            'POST',
            $mappedProducts,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function deleteProducts(array $products, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }
        $this->wizzyWebhookAPI->broadcast(
            $this->storeAdvancedConfig,
            $credentials,
            WizzyWebhookAPI::TOPIC_BEFORE_PRODUCTS_DELETE,
            $products
        );
        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getDeleteProductsEndpoint(),
            'DELETE',
            $products,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function saveDefaultCurrency($currencyCode, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send($this->wizzyAPIEndpoints->getSetDefaultCurrencyEndpoint(), 'PUT', [
        'code' => $currencyCode
        ], $this->authHeaders->getFromArray($credentials, true));
    }

    public function saveDisplayCurrency($currencyCode, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send($this->wizzyAPIEndpoints->getSetDisplayCurrencyEndpoint(), 'PUT', [
         'code' => $currencyCode
        ], $this->authHeaders->getFromArray($credentials, true));
    }

    public function saveCurrencies($currencies, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSaveCurrenciesEndpoint(),
            'POST',
            $currencies,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function savePages($pages, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSavePagesEndpoint(),
            'POST',
            $pages,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function saveCurrencyRates($currencyRates, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSaveCurrenciesRatesEndpoint(),
            'POST',
            $currencyRates,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function deleteCurrencies($currencies, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getDeleteCurrenciesEndpoint(),
            'DELETE',
            $currencies,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function deletePages($pages, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getDeletePagesEndpoint(),
            'DELETE',
            $pages,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function getCurrencies($storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getCurrenciesEndpoint(),
            'GET',
            [],
            $this->authHeaders->getFromArray($credentials)
        );
    }

    public function getPages($storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getPagesEndpoint(),
            'GET',
            [],
            $this->authHeaders->getFromArray($credentials, true)
        );
    }

    private function getStoreCredentials($storeId)
    {
        $credentials = $this->storeManager->getCredentials($storeId);

        if ($credentials == null ||
           empty($credentials['storeId']) ||
           empty($credentials['storeSecret']) ||
           empty($credentials['apiKey'])
        ) {
            return false;
        }

        return $credentials;
    }

    public function getSynonyms($storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSynonymsEndpoint(),
            'GET',
            [],
            $this->authHeaders->getFromArray($credentials, true)
        );
    }

    public function addSynonyms($storeId, $payload): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSynonymsEndpoint(),
            'POST',
            $payload,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function deleteSynonyms($storeId, $payload): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        $endPoint = $this->wizzyAPIEndpoints->getSynonymsEndpoint();
        $endPoint = $endPoint . $payload;
        
        return $this->wizzyApiConnector->send(
            $endPoint,
            'delete',
            [],
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }

    public function editSynonyms($storeId, $payload)
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            $this->wizzyAPIEndpoints->getSynonymsEndpoint(),
            'PUT',
            $payload,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }
    private function getTransformedProductsFromWebhook(array $products, $credentials)
    {
        $response = $this->wizzyWebhookAPI->broadcast(
            $this->storeAdvancedConfig,
            $credentials,
            WizzyWebhookAPI::TOPIC_BEFORE_PRODUCTS_SYNC,
            $products
        );
        if (is_array($response)) {
            return $response;
        }

        return $products;
    }
}
