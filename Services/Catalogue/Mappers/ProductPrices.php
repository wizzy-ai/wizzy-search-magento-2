<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\Catalog\Helper\Data as TexHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;
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

    private $incTax = null;
    private $storeCatalogueConfig;
    private $hasToUseMsrp = false;

    public function __construct(
        CurrencyManager $currencyManager,
        PriceCurrencyInterface $priceHelper,
        StoreTaxConfig $storeTaxConfig,
        TexHelper $taxHelper,
        StoreCatalogueConfig $storeCatalogueConfig
    ) {
        $this->productPrices = [

        ];
        $this->currencyManager = $currencyManager;
        $this->priceHelper = $priceHelper;
        $this->storeTaxConfig = $storeTaxConfig;
        $this->taxHelper = $taxHelper;
        $this->storeCatalogueConfig = $storeCatalogueConfig;
    }

    public function setStore($storeId)
    {
        $this->storeId = $storeId;
        $this->storeTaxConfig->setStore($storeId);
        $this->storeCatalogueConfig->setStore($storeId);
        $this->defaultCurrency = $this->currencyManager->getDefaultCurrency($this->storeId);
        $this->productPrices[$storeId] = [
           self::PRODUCT_PRICE_FINAL_TYPE => [],
           self::PRODUCT_PRICE_ORIGINAL_TYPE => [],
           self::PRODUCT_PRICE_SELLING_TYPE => [],
        ];

        if ($this->storeTaxConfig->isCatalogPriceIncludeTax() === false) {
            if ($this->storeTaxConfig->getTaxCatalogPricesDisplayType() ==
              \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
                $this->incTax = true;
            }
        }

        $this->hasToUseMsrp = $this->storeCatalogueConfig->hasToUseMsrpAsOriginalPrice();
    }

    public function getOriginalPrice($product)
    {
        if (isset($this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()])) {
            return $this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()];
        }

        $originalPrice = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $originalPrice = $this->getDefaultCurrncyValue($originalPrice);

        if ($this->hasToUseMsrp) {
            $originalMsrpPrice = $this->taxHelper->getTaxPrice($product, $product->getMsrp(), $this->incTax);
            if ($originalMsrpPrice) {
                $originalPrice = $originalMsrpPrice;
            }
        }

        $this->productPrices[$this->storeId][self::PRODUCT_PRICE_ORIGINAL_TYPE][$product->getId()] = $originalPrice;

        return $originalPrice;
    }

    public function getFinalPrice($product)
    {
        if (isset($this->productPrices[$this->storeId][self::PRODUCT_PRICE_FINAL_TYPE][$product->getId()])) {
            return $this->productPrices[$this->storeId][self::PRODUCT_PRICE_FINAL_TYPE][$product->getId()];
        }

        $priceMethod = "getBaseAmount";
        if ($this->incTax || $this->storeTaxConfig->isCatalogPriceIncludeTax()) {
            $priceMethod = "getValue";
        }
        $specialPrice = $product->getPriceInfo()->getPrice('special_price')->getAmount()->$priceMethod();
        $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->$priceMethod();

        if ($specialPrice !== false && $specialPrice !== 0 && $specialPrice < $finalPrice) {
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

        $sellingPrice = $this->taxHelper->getTaxPrice($product, $finalPrice, $this->incTax);
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
