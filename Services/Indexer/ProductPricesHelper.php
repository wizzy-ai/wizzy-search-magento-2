<?php

namespace Wizzy\Search\Services\Indexer;

use DateTime;
use Wizzy\Search\Services\Catalogue\CatalogRulePriceManager;
use Wizzy\Search\Services\Model\ProductPrices;
use Wizzy\Search\Services\Catalogue\ProductsManager;

class ProductPricesHelper
{
    private $catalogRulePriceManager;
    private $productPrices;
    private $indexer;
    private $productsManager;

    public function __construct(
        CatalogRulePriceManager $catalogRulePriceManager,
        ProductPrices $productPrices,
        IndexerManager $indexerManager,
        ProductsManager $productsManager
    ) {
        $this->catalogRulePriceManager = $catalogRulePriceManager;
        $this->productPrices = $productPrices;
        $this->indexer = $indexerManager;
        $this->productsManager = $productsManager;
    }

    public function markAllProductPricesSynced()
    {
        $storePrices = $this->catalogRulePriceManager->getAll();
        $wizzyPrices = [];
        list($rulePrices, $productsToAddInSync) = $this->getRulePricesToModify($storePrices, $wizzyPrices);
        $this->productPrices->deleteAll();
        $this->productPrices->addRulePrices($rulePrices);
    }

    public function addUpdatedProductPricesInSync()
    {
        $storePrices = $this->catalogRulePriceManager->getAll();
        $wizzyPrices = $this->productPrices->getAll();

        list($rulePrices, $productsToAddInSync) = $this->getRulePricesToModify($storePrices, $wizzyPrices);

        foreach ($wizzyPrices as $ruleId => $wizzyPrice) {
            if (!isset($storePrices[$ruleId])) {
                $productsToAddInSync[$ruleId] = [
                    'id' => $ruleId,
                    'data' => $wizzyPrice['data'],
                    'product_id' => $wizzyPrice['product_id'],
                ];
            }
        }
        $updatedSpecialPriceProductIds = $this->productsManager->getUpdatedSpecialPriceProductIds();
        $productIds = array_unique(array_column($productsToAddInSync, 'product_id'));
        $productIds = array_unique(array_merge($productIds, $updatedSpecialPriceProductIds));
        $this->productPrices->deleteAll();
        $this->productPrices->addRulePrices($rulePrices);
        $this->indexer->getProductsIndexer()->reindexList($productIds);
    }

    private function getRulePricesToModify($storePrices, $wizzyPrices)
    {
        $productsToAddInSync = [];
        $rulePrices = [];

        $startDate = new DateTime();
        $endDate = (clone $startDate)->modify("-1 day")->format("Y-m-d");
        $startDate = $startDate->format("Y-m-d");

        foreach ($storePrices as $ruleId => $storePrice) {
            $productPriceData = $this->catalogRulePriceManager->getPriceData($storePrice);
            $productId = $storePrice['product_id'];

            $rulePrices[$ruleId] = [
                'id' => $ruleId,
                'data' => $productPriceData,
                'product_id' => $productId,
            ];

            if (!isset($wizzyPrices[$ruleId]) ||
                $wizzyPrices[$ruleId]['data'] != $productPriceData ||
                $storePrice['latest_start_date'] == $startDate ||
                $storePrice['earliest_end_date'] == $endDate
            ) {
                $productsToAddInSync[$ruleId] = $rulePrices[$ruleId];
            }
        }

        return [
            $rulePrices,
            $productsToAddInSync
        ];
    }
}
