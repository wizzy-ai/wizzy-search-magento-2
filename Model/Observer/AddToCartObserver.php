<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\Catalogue\Mappers\ProductPrices;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Session\UserSessionManager;
use Wizzy\Search\Services\Store\StoreManager;

class AddToCartObserver implements ObserverInterface
{
    private $configurable;
    private $productsManager;
    private $userSessionManager;
    private $output;
    private $productPrices;
    private $storeManager;

    public function __construct(
        Configurable $configurable,
        StoreManager $storeManager,
        ProductPrices $productPrices,
        IndexerOutput $output,
        ProductsManager $productsManager,
        UserSessionManager $userSessionManager
    ) {
        $this->configurable = $configurable;
        $this->productsManager = $productsManager;
        $this->userSessionManager = $userSessionManager;
        $this->output = $output;
        $this->productPrices = $productPrices;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer)
    {
        try {
            $event = $observer->getEvent();
            $eventData = $event->getRequest()->getParams();
            $product = $this->productsManager->getById($eventData['product']);
            $this->productPrices->setStore($this->storeManager->getCurrentStoreId());

            $data = [
            'product'    => $eventData['product'],
            'price'      => $this->productPrices->getSellingPrice($product),
            'qty'        => (isset($eventData['qty'])) ? $eventData['qty'] : 1,
            'searchResponseId' =>  (isset($eventData['searchResponseId'])) ? $eventData['searchResponseId'] : '',
            ];
            if ($data['searchResponseId'] !== "") {
                $this->userSessionManager->addClick($data['product'], $data['searchResponseId']);
            }

            if (isset($eventData['super_attribute'])) {
                $variant = $this->configurable->getProductByAttributes($eventData['super_attribute'], $product);

                if ($variant) {
                    $data['variant'] = $variant->getId();

                    if ($data['searchResponseId'] !== "") {
                        $this->userSessionManager->addClick($data['variant'], $data['searchResponseId']);
                    }
                }
            }

            $this->userSessionManager->addInQueue($data, UserSessionManager::ADDED_TO_CART);
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
