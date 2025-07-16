<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Services\API\Wizzy\StoreConnector;
use Wizzy\Search\Services\Config\WizzyCredentials;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;

class WizzyStoreCredentialsChanged implements ObserverInterface
{
    private $request;
    private $messageManager;
    private $storeConnector;
    private $storeManager;
    private $wizzyCredentialsConfig;
    private $storeGeneralConfig;

    public function __construct(
        RequestInterface $request,
        FlashMessagesManager $flashMessagesManager,
        StoreConnector $storeConnector,
        StoreManager $storeManager,
        WizzyCredentials $wizzyCredentials,
        StoreGeneralConfig $storeGeneralConfig
    ) {
        $this->request = $request;
        $this->messageManager = $flashMessagesManager;
        $this->storeConnector = $storeConnector;
        $this->storeManager = $storeManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->wizzyCredentialsConfig = $wizzyCredentials;
    }

    public function execute(EventObserver $observer)
    {
        $storeCredentials = $this->request->getParam('groups');
        $storeCredentials = $storeCredentials['store_credentials']['fields'];

        $storeId = $storeCredentials['store_id']['value'];
        $storeSecret = $storeCredentials['store_secret']['value'];
        $storeAPIKey = $storeCredentials['api_key']['value'];

        if (empty(trim($storeId)) || empty(trim($storeAPIKey)) || empty(trim($storeSecret))) {
            $this->messageManager->warning(
                'API Key, Store ID, and Secret are required to communicate with Wizzy\'s server.'
            );
            return $this;
        }

        $currentStoreId = $observer->getData('store');
        $this->storeGeneralConfig->setStore($currentStoreId);

       // Verifying the store credentials.
        if ($this->storeConnector->auth($storeId, $storeAPIKey, $storeSecret)) {
            $this->wizzyCredentialsConfig->onCredentialsSet();
            $this->messageManager->success(
                'Successfully connected to Wizzy, 
                You must follow the next setup steps to enable search on store.'
            );
        } else {
            $this->messageManager->error(
                'There were some problem connecting to the Wizzy, 
                Please check the store credentials.'
            );
        }

        return $this;
    }
}
