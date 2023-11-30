<?php

namespace Wizzy\Search\Services\Model;

use Wizzy\Search\Helpers\DB\WizzyTables;
use Wizzy\Search\Services\DB\ConnectionManager;
use Wizzy\Search\Model\DeletedProductsFactory;

class DeletedProducts
{
    private $connectionManager;
    private $deletedProductsFactory;

    public function __construct(
        ConnectionManager $connectionManager,
        DeletedProductsFactory $deletedProductsFactory
    ) {
        $this->connectionManager = $connectionManager;
        $this->deletedProductsFactory = $deletedProductsFactory;
    }

    public function addDeletedProducts($productIds)
    {
        if (count($productIds) === 0) {
            return true;
        }

        $recordsToInsert = [];
        foreach ($productIds as $productId) {
            $recordsToInsert[] = [
            'product_id'   => $productId,
            'status'    => 0,
            ];
        }
        
        $this->connectionManager->insertMultiple(WizzyTables::$PRODUCT_DELETE_TABLE_NAME, $recordsToInsert);
        return true;
    }

    public function getDeletedProducts()
    {
        $collection = $this->deletedProductsFactory->create()->getCollection()
            ->addFieldToSelect('*');

        $deletedProducts = [];
        foreach ($collection as $deletedProduct) {
            $data = $deletedProduct->getData();
            $deletedProducts[$data['product_id']] = $deletedProduct;
        }
        return $deletedProducts;
    }

    public function removeDeletedProducts($productIds)
    {
        if (count($productIds) === 0) {
            return true;
        }

        $entities = $this->deletedProductsFactory->create()->getCollection()
         ->addFieldToFilter('product_id', ["in" => $productIds]);

        $entities = $entities->setOrder('id', 'ASC');
        $entities->walk('delete');

        return $entities;
    }
}
