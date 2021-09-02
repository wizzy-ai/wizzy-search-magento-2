<?php

namespace Wizzy\Search\Controller\Analytics;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Wizzy\Search\Services\API\Wizzy\Modules\Analytics;
use Wizzy\Search\Services\Session\UserSessionManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class Collect extends Action
{
    private $jsonFactory;
    private $storeManager;
    private $storeGeneralConfig;
    private $analytics;
    private $userSessionManager;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        UserSessionManager $userSessionManager,
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        Analytics $analytics
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->storeManager = $storeManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->analytics = $analytics;
        $this->userSessionManager = $userSessionManager;
        return parent::__construct($context);
    }

    public function execute()
    {
        $returnPayload = [
         'success' => true,
         'userId'  => '',
        ];

        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getMethod() === "POST") {
            $content = $this->getRequest()->getContent();
            try {
                $content = json_decode($content, true);
            } catch (\Exception $e) {
                $content = false;
            }

            if ($content !== false && $content !== null) {
                if (isset($content['type']) && isset($content['data']) && is_array($content['data'])) {

                    $currentStoreId = $this->storeManager->getCurrentStoreId();
                    $this->storeGeneralConfig->setStore($currentStoreId);
                    $headers = (isset($content['headers'])) ? $content['headers'] : [];
                    $loggedInUserId = $this->userSessionManager->getLoggedInUserId();

                    if ((isset($headers['x-wizzy-userId']) &&
                          $loggedInUserId != "" &&
                          $headers['x-wizzy-userId'] != $loggedInUserId) ||
                    ((!isset($headers['x-wizzy-userId']) ||
                          $headers['x-wizzy-userId'] == '') &&
                       $loggedInUserId != "")
                    ) {
                        $headers['x-wizzy-userId'] = $loggedInUserId;
                        $returnPayload['userId'] = $loggedInUserId;
                    }

                    if ($this->storeGeneralConfig->isAnalyticsEnabled()) {
                        if ($content['type'] === 'view') {
                            $this->analytics->collectView($content['data'], $currentStoreId, $headers);
                        }

                        if ($content['type'] === 'click') {
                            $this->addSessionProductClicks($content['data']);
                            $this->analytics->collectClick($content['data'], $currentStoreId, $headers);
                        }
                    }
                }
            }
        }

        $result = $this->jsonFactory->create();
        $result->setData($returnPayload);

        return $result;
    }

    private function addSessionProductClicks(array $data)
    {
        if (isset($data['items']) &&
           isset($data['searchResponseId']) &&
           is_array($data['items']) &&
           isset($data['name']) &&
           $data['name'] === Analytics::NAMES[UserSessionManager::SEARCH_RESULTS_CLICKED]) {
            foreach ($data['items'] as $item) {
                if (isset($item['itemId'])) {
                    $this->userSessionManager->addClick($item['itemId'], $data['searchResponseId']);
                }
            }
        }
    }
}
