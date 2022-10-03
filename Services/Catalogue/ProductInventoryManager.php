<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ProductInventoryManager
{
    /**
     * @var SourceItemRepositoryInterface
     */
    protected $sourceItems;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param SourceItemRepositoryInterface $sourceItems
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        SourceItemRepositoryInterface $sourceItems,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->sourceItems = $sourceItems;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function getData($product, $sourceCode)
    {
        $sku = $product->getSku();
        $data =
        [
            'inStock' => true,
            'qty' => 1,
        ];

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('sku', $sku)->create();
        $sourceItemData = $this->sourceItems->getList($searchCriteria);

        if ($sourceItemData->getItems()) {
            foreach ($sourceItemData->getItems() as $sourceItem) {
                if ($sourceItem->getSourceCode() == $sourceCode) {
                        $data =
                        [
                            'inStock' => ($sourceItem->getStatus() == 1),
                            'qty' => $sourceItem->getQuantity(),
                        ];
                        break;
                }
            }
        }
        return $data;
    }
}
