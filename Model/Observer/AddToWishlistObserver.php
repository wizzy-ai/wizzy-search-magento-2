<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Wizzy\Search\Services\Catalogue\Mappers\ProductPrices;
use Magento\Framework\App\RequestInterface;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Session\UserSessionManager;
use Wizzy\Search\Services\Store\StoreManager;

class AddToWishlistObserver implements ObserverInterface
{
    private $userSessionManager;
    private $output;
    private $productPrices;
    private $storeManager;
    private $request;

    public function __construct(
        StoreManager $storeManager,
        ProductPrices $productPrices,
        IndexerOutput $output,
        UserSessionManager $userSessionManager,
        RequestInterface $request
    ) {
        $this->userSessionManager = $userSessionManager;
        $this->output = $output;
        $this->productPrices = $productPrices;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            $product = $event->getData('product');
            $searchResponseId = $this->request->getParam('searchResponseId');

            $this->productPrices->setStore($this->storeManager->getCurrentStoreId());

            $data = [
            'product'    => $product->getId(),
            'price'      => $this->productPrices->getSellingPrice($product),
            'qty'        => 1,
            'searchResponseId' =>  ($searchResponseId) ? $searchResponseId : '',
            ];

            $this->userSessionManager->addInQueue($data, UserSessionManager::ADDED_TO_WISHLIST);
        } catch (\Exception $exception) {
            $this->output->log([
            'Message'  => $exception->getMessage(),
            'Trace' => $exception->getTraceAsString(),
            'Class' => get_class($exception),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            ]);
        }
    }
}
