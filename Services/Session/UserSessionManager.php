<?php

namespace Wizzy\Search\Services\Session;

use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class UserSessionManager
{
    const SESSION_QUEUE = "SessionQueue";
    const SESSION_PRODUCT_CLICKS = "SessionClicks";

    const ADDED_TO_CART = "ADDED_TO_CART";
    const ADDED_TO_WISHLIST = "ADDED_TO_WISHLIST";
    const PRODUCTS_PURCHASED = "PRODUCTS_PURCHASED";
    const SEARCH_RESULTS_CLICKED = "SEARCH_RESULTS_CLICKED";

    const WIZZY_SESSION_QUEUE = "WIZZY_SESSION_QUEUE";

    private $sessionManager;
    private $cookieManager;
    private $cookieMetadataFactory;

    public function __construct(
        Session $sessionManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        $this->sessionManager = $sessionManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    private function getSessionQueue()
    {
        $func = "get" . self::SESSION_QUEUE;
        $queue = $this->sessionManager->$func();
        if ($queue === null) {
            $queue = [];
        }

        return $queue;
    }

    private function getSessionClicks()
    {
        $func = "get" . self::SESSION_PRODUCT_CLICKS;
        $clicks = $this->sessionManager->$func();
        if ($clicks === null) {
            $clicks = [];
        }

        return $clicks;
    }

    public function getLoggedInUserId()
    {
        $customer = $this->sessionManager->getCustomer();
        return ($customer && $customer->getId()) ? $customer->getId() : '';
    }

    public function dequeue($limit = 10)
    {
        $queue = $this->getSessionQueue();
        $items = array_splice($queue, 0, 10);
        $this->setSessionQueue($queue);

        return $items;
    }

    public function addInQueue(array $data, $key)
    {
        $queue = $this->getSessionQueue();

        $queue[] = [
         'data' => $data,
         'key'  => $key,
        ];

        $this->setSessionQueue($queue);
    }

    private function setSessionQueue(array $queue)
    {
        $func = "set" . self::SESSION_QUEUE;
        $this->sessionManager->$func($queue);
        $this->setSessionCookie($queue);
    }

    private function setSessionCookie(array $list)
    {
        $meta = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $meta->setDurationOneYear();
        $meta->setPath('/');
        $meta->setHttpOnly(false);

        if (count($list)) {
            $this->cookieManager->setPublicCookie(self::WIZZY_SESSION_QUEUE, "1", $meta);
        } else {
            $this->cookieManager->deleteCookie(self::WIZZY_SESSION_QUEUE, $meta);
        }
    }

    public function addClick($productId, $searchResponseId)
    {
        $clicks = $this->getSessionClicks();
        $clicks[$productId] = [
         'searchResponseId' => $searchResponseId,
        ];
        $this->setSessionClicks($clicks);
    }

    public function getResponseIdFromClicks($productId)
    {
        $clicks = $this->getSessionClicks();
        if (isset($clicks[$productId])) {
            return $clicks[$productId]['searchResponseId'];
        }

        return '';
    }

    private function setSessionClicks(array $clicks)
    {
        $func = "set" . self::SESSION_PRODUCT_CLICKS;
        $this->sessionManager->$func($clicks);
        $this->setSessionCookie($clicks);
    }
}
