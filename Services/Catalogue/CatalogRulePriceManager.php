<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price\CollectionFactory;
use Wizzy\Search\Services\Store\StoreManager;

class CatalogRulePriceManager
{
    private $rulePriceCollection;
    private $storeManager;

    public function __construct(
        CollectionFactory $collection,
        StoreManager $storeManager
    ) {
        $this->rulePriceCollection = $collection;
        $this->storeManager = $storeManager;
    }

    public function getAll()
    {
        $collection = $this->rulePriceCollection->create()
            ->addFieldToSelect('*');

        $rulePrices = [];
        $websiteIds = array_flip($this->storeManager->getActiveWizzyWebsites());

        foreach ($collection as $rulePrice) {
            $data = $rulePrice->getData();
            $websiteId = $data['website_id'];
            if (isset($websiteIds[$websiteId])) {
                $rulePrices[$data['rule_product_price_id']] = $data;
            }
        }

        return $rulePrices;
    }

    public function getPriceData($storePrice)
    {
        $data = $storePrice['rule_date'] .
            $storePrice['customer_group_id'] .
            $storePrice['product_id'] .
            $storePrice['rule_price'] .
            $storePrice['website_id'];

        if (isset($storePrice['latest_start_date']) && $storePrice['latest_start_date']) {
            $data .= $storePrice['latest_start_date'];
        }

        if (isset($storePrice['earliest_end_date']) && $storePrice['earliest_end_date']) {
            $data .= $storePrice['earliest_end_date'];
        }

        return $data;
    }
}
