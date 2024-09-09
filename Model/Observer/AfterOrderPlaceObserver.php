<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterOrderPlaceObserver extends BaseOrdersObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $this->addOrderProductsInSync($order);
    }
}
