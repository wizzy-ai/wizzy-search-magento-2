<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory;

class OrderItemManager
{
    private $orderItemCollection;

    public function __construct(
        CollectionFactory $orderItemCollection
    ) {
        $this->orderItemCollection = $orderItemCollection;
    }

    public function getSummaryByProductIds($productIds, $storeId)
    {

        $orderItemsCollection = $this->orderItemCollection->create();

        $orderItemsCollection
          ->addFieldToSelect(OrderItemInterface::ITEM_ID)
          ->addFieldToSelect(OrderItemInterface::PRODUCT_ID)
          ->getSelect()
          ->columns(
              [
                'COUNT('.OrderItemInterface::QTY_INVOICED.') AS totalQtyInvoiced',
                'COUNT(DISTINCT '.OrderItemInterface::ORDER_ID.') AS totalOrders'
              ]
          )
          ->where(OrderItemInterface::PRODUCT_ID . ' IN (' .implode(",", $productIds). ')')
          ->where(OrderItemInterface::STORE_ID. ' = ' . $storeId)
          ->group(OrderItemInterface::PRODUCT_ID);

        return $orderItemsCollection->getData();
    }

    public function getSummary($productIds, $storeId)
    {
        $productOrdersSummry = [];
        foreach ($productIds as $productId) {
            $productOrdersSummry[$productId] = [
            'orders' => 0,
            'qty'    => 0,
            ];
        }

        $orderItemsSummary = $this->getSummaryByProductIds($productIds, $storeId);

        foreach ($orderItemsSummary as $orderItemSummary) {
            if (isset($orderItemSummary['product_id'])) {
                $productId = $orderItemSummary['product_id'];
                if (!empty($productId) && $productId !== null && isset($productOrdersSummry[$productId])) {
                    $productOrdersSummry[$productId]['orders'] = $orderItemSummary['totalOrders'];
                    $productOrdersSummry[$productId]['qty'] = $orderItemSummary['totalQtyInvoiced'];
                }
            }
        }

        return $productOrdersSummry;
    }
}
