<?php

namespace Wizzy\Search\Controller\Analytics;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Wizzy\Search\Services\API\Wizzy\Modules\Analytics;
use Wizzy\Search\Services\Session\UserSessionManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class Session extends Action
{
    private $jsonFactory;
    private $storeManager;
    private $storeGeneralConfig;
    private $analytics;
    private $userSessionManager;

    public function __construct(
        Context $context,
        UserSessionManager $userSessionManager,
        JsonFactory $jsonFactory,
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
        $loggedInUserId = $this->userSessionManager->getLoggedInUserId();

        $returnPayload = [
         'success' => true,
         'userId'  => $loggedInUserId,
        ];

        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->getMethod() === "POST") {
            $content = $this->getRequest()->getContent();
            try {
                $content = json_decode($content, true);
            } catch (\Exception $e) {
                $content = false;
            }

            if ($content !== false && $content !== null) {
                if (isset($content['headers']) && isset($content['headers']) && is_array($content['headers'])) {

                    $currentStoreId = $this->storeManager->getCurrentStoreId();
                    $this->storeGeneralConfig->setStore($currentStoreId);

                    if ($this->storeGeneralConfig->isAnalyticsEnabled()) {
                        $headers = $content['headers'];

                        if ((isset($headers['x-wizzy-userId']) &&
                              $loggedInUserId != "" &&
                              $headers['x-wizzy-userId'] != $loggedInUserId) ||
                         ((!isset($headers['x-wizzy-userId']) ||
                               $headers['x-wizzy-userId'] == '') &&
                            $loggedInUserId != "")
                        ) {
                            $headers['x-wizzy-userId'] = $loggedInUserId;
                        }

                        $items = $this->userSessionManager->dequeue();
                        foreach ($items as $item) {
                            list ($type, $payload) = $this->getPayloadAndType($item);
                            $this->analytics->$type($payload, $currentStoreId, $headers);
                        }
                    }

                }
            }
        }

        $result = $this->jsonFactory->create();
        $result->setData($returnPayload);

        return $result;
    }

    private function getPayloadAndType(array $queueItem)
    {
        $payload = [];
        $type = "";

        if ($queueItem['key'] == UserSessionManager::ADDED_TO_CART) {
            $type = Analytics::TYPES[UserSessionManager::ADDED_TO_CART];
            $payload = $this->getCommonConvertedPayload($queueItem, UserSessionManager::ADDED_TO_CART);
            if (isset($queueItem['data']['variant'])) {
                $payload['items'][] = [
                 'itemId' => $queueItem['data']['variant']
                ];
            }
        }

        if ($queueItem['key'] == UserSessionManager::PRODUCTS_PURCHASED) {
            $type = Analytics::TYPES[UserSessionManager::PRODUCTS_PURCHASED];
            $queueItem['data']['name'] = Analytics::NAMES[UserSessionManager::PRODUCTS_PURCHASED];
            $payload = $queueItem['data'];
        }

        if ($queueItem['key'] == UserSessionManager::ADDED_TO_WISHLIST) {
            $type = Analytics::TYPES[UserSessionManager::ADDED_TO_WISHLIST];
            $payload = $this->getCommonConvertedPayload($queueItem, UserSessionManager::ADDED_TO_WISHLIST);
        }

        if (!$payload['searchResponseId']) {
            $itemIds = array_column($payload['items'], 'itemId');
            foreach ($itemIds as $itemId) {
                $searchResponseId = $this->userSessionManager->getResponseIdFromClicks($itemId);
                if ($searchResponseId !== '') {
                    $payload['searchResponseId'] = $searchResponseId;
                    break;
                }
            }
        }

        return [
         $type,
         $payload,
        ];
    }

    private function getCommonConvertedPayload($queueItem, $type)
    {
        return [
         'name' => Analytics::NAMES[$type],
         'items' => [
            [
               'itemId' => $queueItem['data']['product'],
               'qty'    => $queueItem['data']['qty'],
            ]
         ],
         'searchResponseId' => $queueItem['data']['searchResponseId'],
         'value' => ($queueItem['data']['price'] * $queueItem['data']['qty']),
         'qty'   => $queueItem['data']['qty'],
        ];
    }
}
