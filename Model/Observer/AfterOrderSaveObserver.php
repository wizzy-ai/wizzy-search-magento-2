<?php
namespace Wizzy\Search\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AfterOrderSaveObserver extends BaseOrdersObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if ($order->getState() == 'complete' || $order->getState() == 'closed') {
            $this->addOrderProductsInSync($order);
        }
    }
}
