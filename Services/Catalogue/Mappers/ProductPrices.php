<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\Catalog\Helper\Data as TexHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Store\StoreTaxConfig;

class ProductPrices
{

    const PRODUCT_PRICE_ORIGINAL_TYPE = "original";
    const PRODUCT_PRICE_FINAL_TYPE = "final";
    const PRODUCT_PRICE_SELLING_TYPE = "selling";

    private $productPrices;
    private $currencyManager;
    private $priceHelper;

    private $storeId;
    private $defaultCurrency;
    private $storeTaxConfig;
    private $taxHelper;

    public function __construct(
        CurrencyManager $currencyManager,
        PriceCurrencyInterface $priceHelper,
        StoreTaxConfig $storeTaxConfig,
        TexHelper $taxHelper
    ) {
        $this->productPrices = [

        ];
        $this->currencyManager = $currencyManager;
        $this->priceHelper = $priceHelper;
        $this->storeTaxConfig = $storeTaxConfig;
        $this->taxHelper = $taxHelper;
    }

    public function setStore($storeId)
    {
        $this->storeId = $storeId;
        $this->storeTaxConfig->setStore($storeId);
        $this->defaultCurrency = $this->currencyManager->getDefaultCurrency($this->storeId);
        $this->productPrices[$storeId] = [
           self::PRODUCT_PRICE_FINAL_TYPE => [],
           self::PRODUCT_PRICE_ORIGINAL_TYPE => [],
           self::PRODUCT_PRICE_SELLING_TYPE => [],
        ];
    }

    public function getOriginalPrice($product)
    {
        if (isset($this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()])) {
            return $this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()];
        }

        $originalPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()] = $originalPrice;

        return $this->getDefaultCurrncyValue($originalPrice);
    }

    public function getFinalPrice($product)
    {
        if (isset($this->productPrices[$this->storeId][self::PRODUCT_PRICE_FINAL_TYPE][$product->getId()])) {
            return $this->productPrices[$this->storeId][self::PRODUCT_PRICE_FINAL_TYPE][$product->getId()];
        }

        $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getAmount()->getBaseAmount();
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getBaseAmount();

        if ($specialPrice !== false && $specialPrice !== 0) {
            $finalPrice = $this->getDefaultCurrncyValue($specialPrice);
        } else {
            $finalPrice = $this->getDefaultCurrncyValue($finalPrice);
        }

        $this->productPrices[$this->storeId][self::PRODUCT_PRICE_FINAL_TYPE][$product->getId()] =  $finalPrice;

        return $finalPrice;
    }

    public function getSellingPrice($product)
    {
        if (isset($this->productPrices[$this->storeId][self::PRODUCT_PRICE_SELLING_TYPE][$product->getId()])) {
            return $this->productPrices[$this->storeId][self::PRODUCT_PRICE_SELLING_TYPE][$product->getId()];
        }

        $finalPrice = $this->getFinalPrice($product);

        $includingTax = null;

        if ($this->storeTaxConfig->isCatalogPriceIncludeTax() === false) {
            if ($this->storeTaxConfig->getTaxCatalogPricesDisplayType() ==
            \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
                $includingTax = true;
            }
        }
        $sellingPrice = $this->taxHelper->getTaxPrice($product, $finalPrice, $includingTax);
        $this->productPrices[$this->storeId][self::PRODUCT_PRICE_SELLING_TYPE][$product->getId()] = $sellingPrice;

        return $sellingPrice;
    }

    private function getDefaultCurrncyValue($price)
    {
        if ($price == 0) {
            return $price;
        }

        $rate = $this->priceHelper->convert($price, $this->storeId) / $price;
        $price = $price / $rate;

        return $this->priceHelper->round($price);
    }
}
