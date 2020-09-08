<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\Catalogue\AttributesManager;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Store\ConfigManager;

class ProductsMapper
{

    private $configurable;
    private $configurableProductsData;
    private $storeId;

    private $attributesToIgnore;
    private $categoriesToIgnore;

    private $attributesManager;

    private $stockRegistry;
    private $productsManager;

    private $productReviews;
    private $orderItems;
    private $configManager;

    public function __construct(
        Configurable $configurable,
        ProductsManager $productsManager,
        ConfigurableProductsData $configurableProductsData,
        AttributesManager $attributesManager,
        StockRegistry $stockRegistry,
        ConfigManager $configManager
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsData = $configurableProductsData;

        $this->attributesManager = $attributesManager;
        $this->stockRegistry = $stockRegistry;
        $this->productsManager = $productsManager;
        $this->productReviews = [];
        $this->orderItems = [];
        $this->configManager = $configManager;
    }

    private function resetEntitiesToIgnore()
    {
        $this->attributesToIgnore = array_flip($this->configurableProductsData->getAttributesToIgnore($this->storeId));
        $this->categoriesToIgnore = array_flip($this->configurableProductsData->getCategoriesToIgnore($this->storeId));
    }

    public function mapAll($products, $productReviews, $orderItems, $storeId)
    {
        $this->storeId = $storeId;
        $this->productReviews = $productReviews;
        $this->orderItems = $orderItems;

        $this->resetEntitiesToIgnore();
        $mappedProducts = [];
        foreach ($products as $product) {
            $mappedProduct = $this->map($product);
            if ($mappedProduct) {
                // Skips invalid product.
                $mappedProducts[] = $mappedProduct;
            }
        }
        return $mappedProducts;
    }

    public function map($product)
    {
        $mappedProduct = [];
        $this->mapBasicDetails($product, $mappedProduct);
        $this->mapChildData($product, $mappedProduct);
        $this->mapConfigurableData($product, $mappedProduct);
        $this->mapCategories($product, $mappedProduct);
        $this->mapImages($product, $mappedProduct);

        if ($mappedProduct['mainImage'] == "" ||
           empty($mappedProduct['categories']) ||
           empty($mappedProduct['sellingPrice']) ||
           $mappedProduct['sellingPrice'] == 0
        ) {
           // Log this event so developer can debug why particular product is being ignored.
            return null;
        }

        $this->mapReviews($product, $mappedProduct);
        $this->mapOrderData($product, $mappedProduct);
        $this->mapDiscounts($product, $mappedProduct);
        $this->mapParentProduct($product, $mappedProduct);
        $this->mapAttributes($product, $mappedProduct, $mappedProduct['inStock']);
        $this->addParentDataInChild($mappedProduct);

        return $mappedProduct;
    }

    private function mapConfigurableData($product, &$mappedProduct)
    {
        $categories = $this->configurableProductsData->getProductCategories($product, $this->storeId);
        $attributes = $this->configurableProductsData->getProductAttributes($product);

        $brand = $this->configurableProductsData->getBrand($categories, $attributes, $this->storeId);
        if ($brand) {
            $mappedProduct['brand'] = $brand['value'];
        }

        $gender = $this->configurableProductsData->getGender($categories, $attributes, $this->storeId);
        if ($gender) {
            $mappedProduct['gender'] = $gender['value'];
        }

        $colors = $this->configurableProductsData->getColors($categories, $attributes, $this->storeId);
        $this->addProductVariationDetails($colors, $product, $mappedProduct['inStock']);
        if (count($colors)) {
            $this->mapColors($mappedProduct, $colors);
        }

        $sizes = $this->configurableProductsData->getSizes($categories, $attributes, $this->storeId);
        $this->addProductVariationDetails($sizes, $product, $mappedProduct['inStock']);
        if (count($sizes)) {
            $this->mapSizes($mappedProduct, $sizes);
        }
    }

    private function mapChildData($product, &$mappedProduct)
    {
        if ($product->getTypeID() == Configurable::TYPE_CODE) {
            $children = $product->getTypeInstance()->getUsedProducts($product);
            if (count($children) == 0) {
                return;
            }
            $childIds = [];

            foreach ($children as $child) {
                $childIds[] = $child->getId();
            }

            $children = $this->productsManager->getProductsByIds($childIds, $this->storeId);

            $finalPrice = 0;

            $discount = 0;
            $discountPercetnage = 0;
            $price = 0;

            $colors = [];
            $sizes = [];

            $mappedProduct['childData'] = [
            'sellingPrices' => [],
            'discounts' => [],
            'discountPercentages' => [],
            'variationsTotalReviews' => [],
            'variationsAvgRatings' => [],
            'prices' => [],
            ];

            foreach ($children as $child) {
                if ($finalPrice < $child->getFinalPrice()) {
                    $finalPrice = $child->getFinalPrice();

                    if ($child->getFinalPrice() < $child->getPrice()) {
                        $discount = ($child->getPrice() - $child->getFinalPrice());
                        $discountPercetnage =
                           100 - sprintf('%0.2f', (($child->getFinalPrice() * 100) / $child->getPrice()));
                        $price = $child->getPrice();
                    }
                }

                $mappedProduct['childData']['sellingPrices'][] = $this->getFloatVal($child->getFinalPrice());
                if ($child->getPrice() && $child->getPrice() > 0) {
                    $mappedProduct['childData']['prices'][] = $this->getFloatVal($child->getPrice());
                }

                if ($child->getFinalPrice() && $child->getFinalPrice() > 0 && $child->getPrice() > 0) {
                    if ($child->getFinalPrice() < $child->getPrice()) {
                        $mappedProduct['childData']['discounts'][] =
                           $this->getFloatVal($child->getPrice() - $child->getFinalPrice());
                        $mappedProduct['childData']['discountPercentages'][] =
                           $this->getFloatVal(
                               100 - sprintf(
                                   '%0.2f',
                                   (($child->getFinalPrice() * 100) / $child->getPrice())
                               )
                           );
                    }
                }

                $stockItem = $this->stockRegistry->getStockItem($child->getId());
                if ($stockItem && $stockItem->getIsInStock()) {
                    $mappedProduct['inStock'] = true;
                }

                $childVisibility = $child->getVisibility();

                if (!$mappedProduct['isSearchable']) {
                    $mappedProduct['isSearchable'] =
                       ($childVisibility == Visibility::VISIBILITY_IN_SEARCH ||
                          $childVisibility == Visibility::VISIBILITY_BOTH) ? true : $mappedProduct['isSearchable'];
                }

                if (!$mappedProduct['isVisibleInCatalog']) {
                    $mappedProduct['isVisibleInCatalog'] = (
                       $childVisibility == Visibility::VISIBILITY_IN_CATALOG ||
                       $childVisibility == Visibility::VISIBILITY_BOTH
                    ) ? true : $mappedProduct['isVisibleInCatalog'];
                }

                $reviews = $this->getReviewsSummry($child);

                if ($reviews != null) {
                    if ($reviews['totalReviews']) {
                        $mappedProduct['childData']['variationsTotalReviews'][] = $reviews['totalReviews'];
                    }
                    if ($reviews['avgRatings']) {
                        $mappedProduct['childData']['variationsAvgRatings'][] = $reviews['avgRatings'];
                    }
                }

                $categories = $this->configurableProductsData->getProductCategories($child, $this->storeId);
                $attributes = $this->configurableProductsData->getProductAttributes($child);

                $childColors = $this->configurableProductsData->getColors($categories, $attributes, $this->storeId);
                $childSizes = $this->configurableProductsData->getSizes($categories, $attributes, $this->storeId);

                $variationInStock = ($stockItem && $stockItem->getIsInStock());
                $this->addProductVariationDetails($childColors, $child, $variationInStock);
                $this->addProductVariationDetails($childSizes, $child, $variationInStock);

                array_push($colors, ...$childColors);
                array_push($sizes, ...$childSizes);

                $this->mapAttributes($child, $mappedProduct, $variationInStock, true);
            }

            if ($finalPrice != 0) {
                $mappedProduct['sellingPrice'] = $this->getFloatVal($finalPrice);
            }

            if ($discount != 0) {
                $mappedProduct['discount'] = $this->getFloatVal($discount);
            }

            if ($discountPercetnage != 0) {
                $mappedProduct['discountPercentage'] = $this->getFloatVal($discountPercetnage);
            }

            if ($price != 0) {
                $mappedProduct['price'] = $this->getFloatVal($price);
            }

            if (count($colors)) {
                $this->mapColors($mappedProduct, $colors);
            }

            if (count($sizes)) {
                $this->mapSizes($mappedProduct, $sizes);
            }
        }
    }

    private function addProductVariationDetails(&$entities, $variation, $isInStock)
    {
        if ($entities) {
            foreach ($entities as $index => $entity) {
                $entities[$index]['inStock'] = $isInStock;
                $entities[$index]['variationId'] = $variation->getId();
            }
        }
    }

    private function addParentDataInChild(&$mappedProduct)
    {
        if (!isset($mappedProduct['childData'])) {
            return;
        }

        $parentChildData = [
         'sellingPrices' => 'sellingPrice',
         'prices' => 'price',
         'discounts' => 'discount',
         'variationsTotalReviews' => 'totalReviews',
         'variationsAvgRatings' => 'avgRatings',
        ];

        foreach ($parentChildData as $childKey => $parentKey) {
            if (isset($mappedProduct[$parentKey]) && $mappedProduct[$parentKey]) {
                $mappedProduct['childData'][$childKey][] = $mappedProduct[$parentKey];
            }

            $mappedProduct['childData'][$childKey] = array_values(array_unique($mappedProduct['childData'][$childKey]));
        }
    }

    private function mapSizes(&$mappedProduct, $sizes)
    {
        if (!isset($mappedProduct['sizes'])) {
            $mappedProduct['sizes'] = [];
        }
        foreach ($sizes as $size) {
            $sizeArr = [
            'value' => $size['value'],
            'variationId' => $size['variationId'],
            'inStock' => $size['inStock'],
            ];
            if (isset($size['swatch'])) {
                $sizeArr['swatch'] = $size['swatch'];
            }
            $mappedProduct['sizes'][] = $sizeArr;
        }
    }

    private function mapColors(&$mappedProduct, $colors)
    {
        if (!isset($mappedProduct['colors'])) {
            $mappedProduct['colors'] = [];
        }
        foreach ($colors as $color) {
            $colorArr = [
            'value' => $color['value'],
            'variationId' => $color['variationId'],
            'inStock' => $color['inStock'],
            ];
            if (isset($color['swatch'])) {
                $colorArr['swatch'] = $color['swatch'];
            }
            $mappedProduct['colors'][] = $colorArr;
        }
    }

    private function mapAttributes($product, &$mappedProduct, $variationInStock, $isForChild = false)
    {
        $attributes = [];
        if (isset($mappedProduct['attributes'])) {
            $attributes = $mappedProduct['attributes'];
        }
        $autocompleteAttributes = $this->configurableProductsData->getAutocompleteAttributes($this->storeId);
        foreach ($product->getAttributes() as $attribute) {
            $id = $attribute->getAttributeId();
            $code = $attribute->getAttributeCode();

            if (isset($this->attributesToIgnore[$id])) {
                continue;
            }
            $label = $attribute->getFrontendLabel();

            $isUserDefined = $attribute->getIsUserDefined();
            $isFilterableInSearch = ($attribute->getIsFilterableInSearch()) ? true : false;
            $isSearchable = ($attribute->getIsSearchable()) ? true : false;
            $isFilterable = ($attribute->getIsFilterable()) ? true: false;

            if (!$this->isSerachableFrontendInputType($attribute)) {
                $isSearchable = false;
            }

            if ($isUserDefined) {
                $value = $this->getAttributeValue($product, $attribute);
            }

            if ($isUserDefined &&
               (
                  $isFilterableInSearch ||
                  $isFilterable ||
                  $isSearchable
               ) &&
               (count($value) > 1 || (count($value) == 1 && !empty($value[0])))
            ) {
                $autocompleteConfig =
                   $this->getAutocompleteConfig($attribute, $autocompleteAttributes);
                $attributeValueToAdd = [
                'value' => $value,
                'inStock' => $variationInStock,
                'variationId' => $product->getId(),
                ];
                $attributeToPush = [
                'id' => (string) $code,
                'name' => $label,
                'values' => [
                  $attributeValueToAdd
                ],
                'isSearchable' => $isSearchable,
                'isFilterable' => ($isFilterableInSearch || $isFilterable),
                'addInAutocomplete' => $autocompleteConfig['addInAutocomplete'],
                'autocompletePosition' => $autocompleteConfig['position'],
                'autocompleteGlue' => $autocompleteConfig['glue'],
                ];
                $swatch = $this->attributesManager->getSwatchDetails($product, $attribute);
                if ($swatch) {
                    $attributeToPush['value']['swatch'] = $swatch;
                }

                if (isset($attributes[$id])) {
                    $attributes[$id]['values'][] = $attributeValueToAdd;
                } else {
                    $attributes[$id] = $attributeToPush;
                }
            }
        }

        if (count($attributes)) {
            if (!$isForChild) {
                $attributes = array_values($attributes);
            }
            $mappedProduct['attributes'] = $attributes;
        }
    }

    private function getAutocompleteConfig($attribute, $autocompleteAttributes)
    {
        $autocompleteConfig = [
         'addInAutocomplete' => false,
         'position' => '',
         'glue' => '',
         'id' => '',
        ];

        if (isset($autocompleteAttributes[$attribute->getAttributeId()])) {
            $autocompleteConfig = $autocompleteAttributes[$attribute->getAttributeId()];
            $autocompleteConfig['addInAutocomplete'] = true;
        }

        return $autocompleteConfig;
    }

    private function isSerachableFrontendInputType($attribute)
    {
        $frontendInputType = $attribute->getFrontendInput();
        $validSearableFrontendTypes = [
         "text",
         "multiselect",
         "select",
        ];

        return in_array(strtolower($frontendInputType), $validSearableFrontendTypes);
    }

    private function getAttributeValue($product, $attribute)
    {
        $value = $attribute->getFrontend()->getValue($product);
        $frontendInputType = $attribute->getFrontendInput();

        if ($frontendInputType == "multiselect") {
            $value = explode(", ", $value);
        } else {
            if (is_object($value)) {
                $value = [(string) $value];
            } else {
                $value = [$value];
            }
        }

        return $value;
    }

    private function mapParentProduct($product, &$mappedProduct)
    {
        $parentProductIds = $this->configurable->getParentIdsByChild($product->getId());
        if (count($parentProductIds)) {
            $parentProductId = array_shift($parentProductIds);
            $mappedProduct['groupId'] = $parentProductId;

            $parentProducts = $this->productsManager->getProductsByIds([$parentProductId], $this->storeId);
            if (count($parentProducts)) {
                foreach ($parentProducts as $parentProduct) {
                    $mappedProduct['url'] = $parentProduct->getUrlModel()->getUrl(
                        $parentProduct,
                        $this->getUrlOptions()
                    );
                    $visibility = $parentProduct->getVisibility();
                    $mappedProduct['isSearchable'] = (
                       $visibility == Visibility::VISIBILITY_IN_SEARCH ||
                       $visibility == Visibility::VISIBILITY_BOTH) ? true : false;
                    $mappedProduct['isVisibleInCatalog'] = (
                       $visibility == Visibility::VISIBILITY_IN_CATALOG ||
                       $visibility == Visibility::VISIBILITY_BOTH) ? true : false;
                    break;
                }
            }
        }
    }

    private function getFloatVal($value)
    {
        if (!empty($value)) {
            return floatval(number_format($value, 2));
        }

        return $value;
    }

    private function getUrlOptions()
    {
        return [
           '_secure' => $this->configManager->hasToUseSecureUrls($this->storeId),
           '_nosid' => true,
        ];
    }

    private function mapBasicDetails($product, &$mappedProduct)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $visibility = $product->getVisibility();
        $mappedProduct = [
         'id' => $product->getId(),
         'name' => $product->getName(),
         'sellingPrice' => $this->getFloatVal($product->getFinalPrice()),
         'description' => $product->getDescription(),
         'url' => $product->getUrlModel()->getUrl($product, $this->getUrlOptions()),
         'inStock' => ($stockItem && $stockItem->getIsInStock()),
         'stockQty' => ($stockItem) ? $stockItem->getQty() : 0,
         'createdAt' => $product->getCreatedAt(),
         'updatedAt' => $product->getUpdatedAt(),
         'isSearchable' => (
            $visibility == Visibility::VISIBILITY_IN_SEARCH ||
            $visibility == Visibility::VISIBILITY_BOTH) ? true : false,
         'isVisibleInCatalog' => (
            $visibility == Visibility::VISIBILITY_IN_CATALOG ||
            $visibility == Visibility::VISIBILITY_BOTH) ? true : false,
        ];

        return $mappedProduct;
    }

    private function mapCategories($product, &$mappedProduct)
    {
        $categories = $this->getCategories($product);
        $mappedProduct['categories'] = $categories;
    }

    private function mapImages($product, &$mappedProduct)
    {
        $imagesAssoc = $this->getImagesAssoc($product);
        $mappedProduct['mainImage'] = $imagesAssoc['mainImage'];
        $mappedProduct['images'] = $imagesAssoc['images'];
    }

    private function mapOrderData($product, &$mappedProduct)
    {
        $summary = $this->getOrdersSummary($product);

        if ($summary != null) {
            $mappedProduct['orderedQty'] = $summary['qty'];
            $mappedProduct['noOfTimesOrdered'] = count($summary['orders']);
        }
    }

    private function mapReviews($product, &$mappedProduct)
    {
        $reviews = $this->getReviewsSummry($product);

        if ($reviews != null) {
            $mappedProduct['totalReviews'] = $reviews['totalReviews'];
            $mappedProduct['avgRatings'] = $reviews['avgRatings'];
        }
    }

    private function mapDiscounts($product, &$mappedProduct)
    {
        if ($product->getFinalPrice() && $product->getFinalPrice() > 0 && $product->getPrice() > 0) {
            if ($product->getFinalPrice() < $product->getPrice()) {
                $mappedProduct['discount'] = $this->getFloatVal($product->getPrice() - $product->getFinalPrice());
                $mappedProduct['discountPercentage'] =
                   $this->getFloatVal(
                       100 -
                        sprintf(
                            '%0.2f',
                            (($product->getFinalPrice() * 100) / $product->getPrice())
                        )
                   );
                $mappedProduct['price'] = $this->getFloatVal($product->getPrice());
            }
        }
    }

    private function getCategories($product)
    {
        $categories = $this->configurableProductsData->getProductCategories($product, $this->storeId);
        $categoriesAssoc = [];

        foreach ($categories as $category) {
            $categoryToAdd = $this->getCategoryArrayToSend($category);
            if ($category['isActive']) {
                $categoriesAssoc[$category['id']] = $categoryToAdd;
            }
        }

        return array_values($categoriesAssoc);
    }

    private function getCategoryArrayToSend($category)
    {
        return [
         'name' => $category['name'],
         'id'  => $category['urlKey'],
         'url' => $category['url'],
         'position' => (int) $category['position'],
         'level'  => (int) $category['level'],
         'description' => $category['description'],
         'parentId' => $category['parentUrlKey'],
         'image' => $category['image'],
         'pathIds' => $category['pathIds'],
         'includeInMenu' => $category['includeInMenu'],
         'isSearchable' => ($category['isSearchable'] && !isset($this->categoriesToIgnore[$category['id']])),
        ];
    }

    private function getReviewsSummry($product)
    {
        if (isset($this->productReviews[$product->getId()])) {
            $productReviews = $this->productReviews[$product->getId()];
        } else {
            $productReviews = null;
        }

        return $productReviews;
    }

    private function getOrdersSummary($product)
    {
        if (isset($this->orderItems[$product->getId()])) {
            $ordersSummary = $this->orderItems[$product->getId()];
        } else {
            $ordersSummary = null;
        }

        return $ordersSummary;
    }

    private function getImagesAssoc($product)
    {
        $images = [];
        $mainImage = "";
        $index = 0;

        foreach ($product->getMediaGalleryImages() as $productImage) {
            $productImageData = $productImage->getData();
            if ($productImageData['disabled'] == 1) {
                continue;
            }
            if ($index == 0) {
                $mainImage = $productImageData['url'];
            } else {
                $images[] = $productImageData['url'];
            }
            $index++;
        }

        return [
         'images' => $images,
         'mainImage' => $mainImage,
        ];
    }
}
