<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class OrderItemManager
{
    private $orderItemRepository;
    private $searchCriteriaInterface;
    private $filterGroup;
    private $filterBuilder;

    public function __construct(
        OrderItemRepositoryInterface $orderItemRepository,
        SearchCriteriaInterface $searchCriteriaInterface,
        FilterGroup $filterGroup,
        FilterBuilder $filterBuilder
    ) {
        $this->orderItemRepository = $orderItemRepository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroup = $filterGroup;
    }

    public function getByProductIds($productIds, $storeId)
    {
        $this->filterGroup->setFilters([
         $this->filterBuilder
            ->setField(OrderItemInterface::PRODUCT_ID)
            ->setConditionType('in')
            ->setValue($productIds)
            ->create(),
         $this->filterBuilder
            ->setField(OrderItemInterface::STORE_ID)
            ->setConditionType('eq')
            ->setValue($storeId)
            ->create(),
        ]);

        $this->searchCriteriaInterface->setFilterGroups([$this->filterGroup]);
        return $this->orderItemRepository->getList($this->searchCriteriaInterface);
    }

    public function getSummary($productIds, $storeId)
    {
        $productOrdersSummry = [];
        foreach ($productIds as $productId) {
            $productOrdersSummry[$productId] = [
            'orders' => [],
            'qty'    => 0,
            ];
        }

        $orderItems = $this->getByProductIds($productIds, $storeId);

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getProductId() && isset($productOrdersSummry[$orderItem->getProductId()])) {
                $qtyToMinus = $orderItem->getQtyToRefund();
                if ($qtyToMinus < $orderItem->getQtyToCancel()) {
                    $qtyToMinus = $orderItem->getQtyToCancel();
                }
                $qty = $orderItem->getQtyToInvoice() - $qtyToMinus;
                $productOrdersSummry[$orderItem->getProductId()]['orders'][$orderItem->getOrder()->getId()] = true;
                $productOrdersSummry[$orderItem->getProductId()]['qty'] += $qty;
            }
        }

        return $productOrdersSummry;
    }
}
