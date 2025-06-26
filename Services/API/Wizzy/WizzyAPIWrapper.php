<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\AuthHeaders;
use Wizzy\Search\Helpers\API\ResponseBuilder;
use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Model\API\Response;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyAPIWrapper
{

    private $storeManager;
    private $wizzyApiConnector;

    private $responseBuilder;
    private $authHeaders;

    public function __construct(
        StoreManager $storeManager,
        WizzyAPIConnector $wizzyApiConnector,
        ResponseBuilder $responseBuilder,
        AuthHeaders $authHeaders
    ) {
        $this->storeManager = $storeManager;
        $this->wizzyApiConnector = $wizzyApiConnector;

        $this->responseBuilder = $responseBuilder;
        $this->authHeaders = $authHeaders;
    }

    public function collectClick(array $clickData, $storeId, array $headers): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(
            WizzyAPIEndPoints::COLLECT_CLICK_EVENT,
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
            WizzyAPIEndPoints::COLLECT_VIEW_EVENT,
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
            WizzyAPIEndPoints::COLLECT_CONVERTED_EVENT,
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

        return $this->wizzyApiConnector->send(
            WizzyAPIEndPoints::SAVE_PRODUCTS,
            'POST',
            $products,
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

        return $this->wizzyApiConnector->send(
            WizzyAPIEndPoints::DELETE_PRODUCTS,
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

        return $this->wizzyApiConnector->send(WizzyAPIEndPoints::SET_DEFAULT_CURRENCY, 'PUT', [
        'code' => $currencyCode
        ], $this->authHeaders->getFromArray($credentials, true));
    }

    public function saveDisplayCurrency($currencyCode, $storeId): Response
    {
        $credentials = $this->getStoreCredentials($storeId);

        if ($credentials === false) {
            return $this->responseBuilder->error('Invalid store credentials.', []);
        }

        return $this->wizzyApiConnector->send(WizzyAPIEndPoints::SET_DISPLAY_CURRENCY, 'PUT', [
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
            WizzyAPIEndPoints::SAVE_CURRENCIES,
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
            WizzyAPIEndPoints::SAVE_PAGES,
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
            WizzyAPIEndPoints::SAVE_CURRENCIES_RATES,
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
            WizzyAPIEndPoints::DELETE_CURRENCIES,
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
            WizzyAPIEndPoints::DELETE_PAGES,
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
            WizzyAPIEndPoints::GET_CURRENCIES,
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
            WizzyAPIEndPoints::GET_PAGES,
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
            WizzyAPIEndPoints::WIZZY_API_SYNONYMS,
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
            WizzyAPIEndPoints::WIZZY_API_SYNONYMS,
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

        $endPoint = WizzyAPIEndPoints::WIZZY_API_SYNONYMS;
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
            WizzyAPIEndPoints::WIZZY_API_SYNONYMS,
            'PUT',
            $payload,
            $this->authHeaders->getFromArray($credentials, true),
            true
        );
    }
}
