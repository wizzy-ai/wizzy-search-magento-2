<?php

namespace Wizzy\Search\Helpers\API;

use Wizzy\Search\Services\Store\StoreGeneralConfig;

class WizzyAPIEndPoints
{
    private $storeGeneralConfig;
    const BASE_END_POINT = "https://api.wizsearch.in/v1";

    public function __construct(
        StoreGeneralConfig $storeGeneralConfig
    ) {
        $this->storeGeneralConfig = $storeGeneralConfig;
    }

    private function getBaseEndpoint()
    {
        return $this->storeGeneralConfig->getCustomEndpoint() ?: self::BASE_END_POINT;
    }
    
    private function getStoresBaseAuth()
    {
        return $this->getBaseEndpoint() . '/stores';
    }
    
    private function getProductsBaseAuth()
    {
        return $this->getBaseEndpoint() . '/products';
    }
    
    private function getCurrenciesBaseAuth()
    {
        return $this->getBaseEndpoint() . '/currencies';
    }
    
    private function getPagesBaseAuth()
    {
        return $this->getBaseEndpoint() . '/pages';
    }
    
    private function getEventsBaseAuth()
    {
        return $this->getBaseEndpoint() . '/events';
    }
    
    public function getStoreAuthEndpoint()
    {
        return $this->getStoresBaseAuth() . '/auth';
    }
    
    public function getSaveProductsEndpoint()
    {
        return $this->getProductsBaseAuth() . '/save';
    }
    
    public function getDeleteProductsEndpoint()
    {
        return $this->getProductsBaseAuth() . '/delete';
    }
    
    public function getSetDefaultCurrencyEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/default-currency';
    }
    
    public function getSetDisplayCurrencyEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/display-currency';
    }
    
    public function getSaveCurrenciesEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/';
    }
    
    public function getCurrenciesEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/';
    }

    public function getSaveCurrenciesRatesEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/rates';
    }
    
    public function getDeleteCurrenciesEndpoint()
    {
        return $this->getCurrenciesBaseAuth() . '/';
    }
    
    public function getSavePagesEndpoint()
    {
        return $this->getPagesBaseAuth() . '/';
    }
    
    public function getPagesEndpoint()
    {
        return $this->getPagesBaseAuth() . '/';
    }
    
    public function getDeletePagesEndpoint()
    {
        return $this->getPagesBaseAuth() . '/';
    }
    
    public function getCollectClickEventEndpoint()
    {
        return $this->getEventsBaseAuth() . '/click';
    }
    
    public function getCollectViewEventEndpoint()
    {
        return $this->getEventsBaseAuth() . '/view';
    }
    
    public function getCollectConvertedEventEndpoint()
    {
        return $this->getEventsBaseAuth() . '/converted';
    }

    public function getSynonymsEndpoint()
    {
        return $this->getBaseEndpoint() . '/synonyms/';
    }
}
