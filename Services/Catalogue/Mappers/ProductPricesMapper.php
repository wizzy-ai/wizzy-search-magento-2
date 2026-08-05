<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Queue\SessionStorage\ProductsSessionStorage;

/**
 * Maps Magento products to Wizzy price-only payload for /products/prices/save.
 * Price calculation and field shape mirror ProductsMapper (without non-price fields).
 */
class ProductPricesMapper
{
    private $configurable;
    private $eventManager;
    private $productPrices;
    private $productsSessionStorage;
    private $output;
    private $storeId;
    private $parentProductsByChildId = [];

    public function __construct(
        Configurable $configurable,
        ManagerInterface $eventManager,
        ProductPrices $productPrices,
        ProductsSessionStorage $productsSessionStorage,
        IndexerOutput $output
    ) {
        $this->configurable = $configurable;
        $this->eventManager = $eventManager;
        $this->productPrices = $productPrices;
        $this->productsSessionStorage = $productsSessionStorage;
        $this->output = $output;
    }

    /**
     * @param \Magento\Catalog\Model\Product[] $products
     * @param int|string $storeId
     * @return array
     */
    public function mapAll($products, $storeId)
    {
        $this->parentProductsByChildId = [];
        $this->storeId = $storeId;
        $this->productPrices->setStore($storeId);

        $mappedProducts = [];
        foreach ($products as $product) {
            $mappedProduct = $this->map($product);
            if ($mappedProduct) {
                $mappedProducts[] = $mappedProduct;
            }
        }

        $dataObject = new DataObject([
            'productPrices' => $mappedProducts,
            'magentoProducts' => $products,
            'storeId' => $storeId,
        ]);
        $this->eventManager->dispatch(
            'wizzy_after_product_prices_mapped',
            ['data' => $dataObject]
        );

        return $dataObject->getDataByKey('productPrices');
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return array|null
     */
    public function map($product)
    {
        $mappedProduct = [];
        $this->mapBasicPriceDetails($product, $mappedProduct);
        $this->mapChildPriceData($product, $mappedProduct);
        $this->mapGroupId($product, $mappedProduct);

        if (empty($mappedProduct['sellingPrice']) || $mappedProduct['sellingPrice'] == 0) {
            $this->output->log([
                'Message' => 'Product Price Skipped',
                'ID' => $product->getId(),
                'Reason' => "Don't have enough required details",
                'Data' => json_encode([
                    'Selling Price' => $mappedProduct['sellingPrice'],
                ]),
            ], IndexerOutput::LOG_INFO_TYPE);

            return null;
        }

        $this->mapDiscounts($product, $mappedProduct);
        $this->addParentPriceDataInChild($mappedProduct);

        return $mappedProduct;
    }

    private function mapBasicPriceDetails($product, &$mappedProduct)
    {
        $finalPrice = $this->productPrices->getFinalPrice($product);
        $sellingPrice = $this->getFloatVal($this->productPrices->getSellingPrice($product));
        $sellingPriceWithoutTax = $this->getFloatVal($finalPrice);

        $mappedProduct = [
            'id' => $product->getId(),
            'sellingPrice' => $sellingPrice,
            'finalPrice' => $sellingPriceWithoutTax,
        ];
    }

    /**
     * Configurable child prices — same price logic as ProductsMapper::mapChildData.
     */
    private function mapChildPriceData($product, &$mappedProduct)
    {
        if ($product->getTypeID() != Configurable::TYPE_CODE) {
            return;
        }

        $children = $product->getTypeInstance()->getUsedProducts($product);
        if (count($children) == 0) {
            return;
        }

        $childIds = [];
        foreach ($children as $child) {
            if (!$child->isDisabled()) {
                $childIds[] = $child->getId();
            }
        }

        $children = $this->productsSessionStorage->getByIds($childIds);

        $finalPrice = 0;
        $sellingPrice = 0;
        $discount = 0;
        $discountPercentage = 0;
        $price = 0;

        $mappedProduct['childData'] = [
            'sellingPrices' => [],
            'discounts' => [],
            'discountPercentages' => [],
            'prices' => [],
            'finalPrices' => [],
        ];

        foreach ($children as $child) {
            $childFinalPrice = $this->productPrices->getFinalPrice($child);
            $childOriginalPrice = $this->productPrices->getOriginalPrice($child);
            $childSellingPrice = $this->productPrices->getSellingPrice($child);

            if ($finalPrice < $childFinalPrice) {
                $finalPrice = $childFinalPrice;
                $sellingPrice = $childSellingPrice;

                if ($childSellingPrice < $childOriginalPrice) {
                    $discount = ($childOriginalPrice - $childSellingPrice);
                    $discountPercentage =
                        round((($childOriginalPrice - $childSellingPrice) / $childOriginalPrice) * 100);
                    $price = $childOriginalPrice;
                }
            }

            $mappedProduct['childData']['sellingPrices'][] = $this->getFloatVal($childSellingPrice);
            $mappedProduct['childData']['finalPrices'][] = $this->getFloatVal($childFinalPrice);

            if ($childOriginalPrice && $childOriginalPrice > 0) {
                $mappedProduct['childData']['prices'][] = $this->getFloatVal($childOriginalPrice);
            }

            if ($childSellingPrice && $childSellingPrice > 0 && $childOriginalPrice > 0) {
                if ($childSellingPrice < $childOriginalPrice) {
                    $mappedProduct['childData']['discounts'][] =
                        $this->getFloatVal($childOriginalPrice - $childSellingPrice);
                    $mappedProduct['childData']['discountPercentages'][] =
                        $this->getFloatVal(
                            round((($childOriginalPrice - $childSellingPrice) / $childOriginalPrice) * 100)
                        );
                }
            }
        }

        if ($finalPrice != 0 && $finalPrice < $mappedProduct['finalPrice']) {
            $mappedProduct['finalPrice'] = $this->getFloatVal($finalPrice);
        }

        if ($sellingPrice != 0 && ($sellingPrice < $mappedProduct['sellingPrice'] ||
                !$mappedProduct['sellingPrice'])) {
            $mappedProduct['sellingPrice'] = $this->getFloatVal($sellingPrice);
        }

        if ($discount != 0) {
            $mappedProduct['discount'] = $this->getFloatVal($discount);
        }

        if ($discountPercentage != 0) {
            $mappedProduct['discountPercentage'] = $this->getFloatVal($discountPercentage);
        }

        if ($price != 0) {
            $mappedProduct['price'] = $this->getFloatVal($price);
        }
    }

    private function mapGroupId($product, &$mappedProduct)
    {
        $parentProduct = $this->getParentProduct($product);
        if ($parentProduct) {
            $mappedProduct['groupId'] = $parentProduct->getId();
        }
    }

    private function getParentProduct($product)
    {
        $productId = $product->getId();
        if (array_key_exists($productId, $this->parentProductsByChildId)) {
            return $this->parentProductsByChildId[$productId];
        }

        $parentProductIds = $this->configurable->getParentIdsByChild($productId);
        if (!count($parentProductIds)) {
            return $this->parentProductsByChildId[$productId] = null;
        }

        $parentProductIds = array_values(array_unique($parentProductIds));
        sort($parentProductIds, SORT_NUMERIC);
        $parentProducts = $this->productsSessionStorage->getByIds($parentProductIds);

        foreach ($parentProducts as $parentProduct) {
            return $this->parentProductsByChildId[$productId] = $parentProduct;
        }

        return $this->parentProductsByChildId[$productId] = null;
    }

    /**
     * Same as ProductsMapper::mapDiscounts — uses Magento product prices, not mapped overrides.
     */
    private function mapDiscounts($product, &$mappedProduct)
    {
        $productFinalPrice = $this->productPrices->getSellingPrice($product);
        $productOriginalPrice = $this->productPrices->getOriginalPrice($product);

        if ($productFinalPrice && $productFinalPrice > 0 &&
            $productOriginalPrice > 0 && $productOriginalPrice > $productFinalPrice) {
            $mappedProduct['discount'] = $this->getFloatVal($productOriginalPrice - $productFinalPrice);
            $mappedProduct['discountPercentage'] =
                $this->getFloatVal(
                    round((($productOriginalPrice - $productFinalPrice) / $productOriginalPrice) * 100)
                );
            $mappedProduct['price'] = $this->getFloatVal($productOriginalPrice);
        }
    }

    /**
     * Same as ProductsMapper::addParentDataInChild for price fields only.
     */
    private function addParentPriceDataInChild(&$mappedProduct)
    {
        if (!isset($mappedProduct['childData'])) {
            return;
        }

        $parentChildData = [
            'sellingPrices' => 'sellingPrice',
            'finalPrices' => 'finalPrice',
            'prices' => 'price',
            'discounts' => 'discount',
            'discountPercentages' => 'discountPercentage',
        ];

        foreach ($parentChildData as $childKey => $parentKey) {
            if (isset($mappedProduct[$parentKey]) && $mappedProduct[$parentKey]) {
                $mappedProduct['childData'][$childKey][] = $mappedProduct[$parentKey];
            }

            $mappedProduct['childData'][$childKey] = array_values(array_unique($mappedProduct['childData'][$childKey]));
        }
    }

    private function getFloatVal($value)
    {
        if (!empty($value)) {
            return floatval(number_format($value, 2, '.', ''));
        }

        return $value;
    }
}
