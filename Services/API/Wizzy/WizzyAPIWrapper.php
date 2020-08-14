<?php

namespace Wizzy\Search\Services\API\Wizzy;

use Wizzy\Search\Helpers\API\AuthHeaders;
use Wizzy\Search\Helpers\API\ResponseBuilder;
use Wizzy\Search\Helpers\API\WizzyAPIEndPoints;
use Wizzy\Search\Model\API\Response;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyAPIWrapper {

  private $storeManager;
  private $wizzyApiConnector;

  private $responseBuilder;

  public function __construct(StoreManager $storeManager, WizzyAPIConnector $wizzyApiConnector, ResponseBuilder $responseBuilder) {
    $this->storeManager = $storeManager;
    $this->wizzyApiConnector = $wizzyApiConnector;

    $this->responseBuilder = $responseBuilder;
  }

  public function saveProducts(array $products, $storeId): Response {
    $credentials = $this->getStoreCredentials($storeId);

    if ($credentials === FALSE) {
      return $this->responseBuilder->error('Invalid store credentials.', []);
    }

    return $this->wizzyApiConnector->send(WizzyAPIEndPoints::saveProducts(), 'POST', $products, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
  }

  public function deleteProducts(array $products, $storeId): Response {
    $credentials = $this->getStoreCredentials($storeId);

    if ($credentials === FALSE) {
      return $this->responseBuilder->error('Invalid store credentials.', []);
    }

    return $this->wizzyApiConnector->send(WizzyAPIEndPoints::deleteProducts(), 'DELETE', $products, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
  }

  public function saveDefaultCurrency($currencyCode, $storeId): Response {
     $credentials = $this->getStoreCredentials($storeId);

     if ($credentials === FALSE) {
        return $this->responseBuilder->error('Invalid store credentials.', []);
     }

     return $this->wizzyApiConnector->send(WizzyAPIEndPoints::setDefaultCurrency(), 'PUT', [
        'code' => $currencyCode
     ], AuthHeaders::headersFromArray($credentials, TRUE));
  }

   public function saveDisplayCurrency($currencyCode, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::setDisplayCurrency(), 'PUT', [
         'code' => $currencyCode
      ], AuthHeaders::headersFromArray($credentials, TRUE));
   }

   public function saveCurrencies($currencies, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::saveCurrencies(), 'POST', $currencies, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
   }

   public function savePages($pages, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::savePages(), 'POST', $pages, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
   }

   public function saveCurrencyRates($currencyRates, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::saveCurrencyRates(), 'POST', $currencyRates, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
   }

   public function deleteCurrencies($currencies, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::deleteCurrencies(), 'DELETE', $currencies, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
   }

   public function deletePages($pages, $storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::deletePages(), 'DELETE', $pages, AuthHeaders::headersFromArray($credentials, TRUE), TRUE);
   }

   public function getCurrencies($storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::getCurrencies(), 'GET', [], AuthHeaders::headersFromArray($credentials));
   }

   public function getPages($storeId): Response {
      $credentials = $this->getStoreCredentials($storeId);

      if ($credentials === FALSE) {
         return $this->responseBuilder->error('Invalid store credentials.', []);
      }

      return $this->wizzyApiConnector->send(WizzyAPIEndPoints::getPages(), 'GET', [], AuthHeaders::headersFromArray($credentials, TRUE));
   }

  private function getStoreCredentials($storeId) {
    $credentials = $this->storeManager->getCredentials($storeId);

    if ($credentials == NULL || empty($credentials['storeId']) || empty($credentials['storeSecret']) || empty($credentials['apiKey'])) {
      return FALSE;
    }

    return $credentials;
  }
}