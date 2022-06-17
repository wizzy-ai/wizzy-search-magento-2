<?php

namespace Wizzy\Search\Services\Model;

use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Model\ProductPricesFactory;
use Wizzy\Search\Services\DB\ConnectionManager;

class ProductPrices
{
    private $productPricesFactory;
    private $connectionManager;

    public function __construct(
        ProductPricesFactory $productPricesFactory,
        ConnectionManager $connectionManager
    ) {
        $this->productPricesFactory = $productPricesFactory;
        $this->connectionManager = $connectionManager;
    }

    public function addRulePrices($rulePrices)
    {
        if (count($rulePrices) === 0) {
            return true;
        }

        $rulePriceChunks = array_chunk($rulePrices, 10000);

        foreach ($rulePriceChunks as $rulePrices) {
            $this->connectionManager->insertMultiple(WizzyTables::$PRODUCT_PRICES, $rulePrices);
        }
        return true;
    }

    public function deleteAll()
    {
        $this->connectionManager->getConnection()->delete(
            $this->connectionManager->getTableName(WizzyTables::$PRODUCT_PRICES),
            '1'
        );
    }

    public function getAll()
    {
        $collection = $this->productPricesFactory->create()->getCollection()
            ->addFieldToSelect('*');

        $rulePrices = [];
        foreach ($collection as $rulePrice) {
            $data = $rulePrice->getData();
            $rulePrices[$data['id']] = $data;
        }

        return $rulePrices;
    }
}
