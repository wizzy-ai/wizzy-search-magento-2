<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Wizzy\Search\Services\Catalogue\AttributesManager;
use Wizzy\Search\Services\Catalogue\ProductImageManager;
use Wizzy\Search\Services\Catalogue\ProductsAttributesManager;
use Wizzy\Search\Services\Catalogue\ProductURLManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Model\SyncSkippedEntities;
use Wizzy\Search\Services\Queue\SessionStorage\ProductsSessionStorage;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;
use Wizzy\Search\Ui\Component\Listing\Column\SkippedEntityData;
use Magento\Backend\Model\Url as BackendUrl;

class ProductsMapper
{

    private $configurable;
    private $configurableProductsData;
    private $storeId;

    private $attributesToIgnore;
    private $categoriesToIgnore;

    private $attributesManager;

    private $stockRegistry;

    private $productReviews;
    private $orderItems;
    private $configManager;
    private $output;
    private $productPrices;
    private $productImageManager;
    private $syncSkippedEntities;
    private $skippedProducts;
    private $storeCatalogueConfig;

    private $isBrandMandatory;
    private $backendUrl;
    private $adminUrl;
    private $commonWordsToRemove;
    private $hasWordsToRemove;
    private $productsAttributesManager;
    private $productURLManager;

    public function __construct(
        Configurable $configurable,
        ConfigurableProductsData $configurableProductsData,
        AttributesManager $attributesManager,
        StockRegistry $stockRegistry,
        ConfigManager $configManager,
        ProductsSessionStorage $productsSessionStorage,
        IndexerOutput $output,
        ProductImageManager $productImageManager,
        ProductPrices $productPrices,
        SyncSkippedEntities $syncSkippedEntities,
        StoreCatalogueConfig $storeCatalogueConfig,
        BackendUrl $backendUrl,
        ProductsAttributesManager $productsAttributesManager,
        ProductURLManager $productURLManager
    ) {
        $this->configurable = $configurable;
        $this->configurableProductsData = $configurableProductsData;

        $this->attributesManager = $attributesManager;
        $this->stockRegistry = $stockRegistry;
        $this->productReviews = [];
        $this->orderItems = [];
        $this->configManager = $configManager;
        $this->productsSessionStorage = $productsSessionStorage;
        $this->output = $output;
        $this->productPrices = $productPrices;
        $this->productImageManager = $productImageManager;
        $this->syncSkippedEntities = $syncSkippedEntities;
        $this->skippedProducts = [];
        $this->storeCatalogueConfig = $storeCatalogueConfig;
        $this->backendUrl = $backendUrl;
        $this->adminUrl = null;
        $this->commonWordsToRemove = null;
        $this->hasWordsToRemove = false;
        $this->productsAttributesManager = $productsAttributesManager;
        $this->productURLManager = $productURLManager;
    }

    private function resetEntitiesToIgnore()
    {
        $this->attributesToIgnore = array_flip($this->configurableProductsData->getAttributesToIgnore($this->storeId));
        $this->categoriesToIgnore = array_flip($this->configurableProductsData->getCategoriesToIgnore($this->storeId));
    }

    private function setAdminUrl()
    {
        $routePath = 'product/edit';
        $this->adminUrl = $this->backendUrl->getUrl($routePath);
        $this->adminUrl = substr($this->adminUrl, 0, strpos($this->adminUrl, $routePath));
    }

    public function mapAll($products, $productReviews, $orderItems, $storeId)
    {
        $this->storeId = $storeId;
        $this->productReviews = $productReviews;
        $this->orderItems = $orderItems;
        $this->productPrices->setStore($storeId);
        $this->storeCatalogueConfig->setStore($storeId);
        $this->productURLManager->setStore($storeId);
        $this->productURLManager->fetchUrls($products);
        $this->setAdminUrl();

        $this->isBrandMandatory = $this->storeCatalogueConfig->isBrandMandatoryForSync();

        $this->resetEntitiesToIgnore();
        $mappedProducts = [];
        $this->productsAttributesManager->setAttributeValues($products);

        foreach ($products as $product) {
            $mappedProduct = $this->map($product);
            if ($mappedProduct) {
                // Skips invalid product.
                $mappedProducts[] = $mappedProduct;
            }
        }

        $this->updateSkippedProducts($mappedProducts);
        return $mappedProducts;
    }

    private function updateSkippedProducts($mappedProducts)
    {
        $mappedProductIds = array_column($mappedProducts, 'id');
        $this->syncSkippedEntities->deleteSkippedEntities($mappedProductIds, $this->storeId);
        $this->syncSkippedEntities->addSkippedEntities(array_values($this->skippedProducts), $this->storeId);
    }

    public function map($product)
    {
        $mappedProduct = [];
        $this->mapBasicDetails($product, $mappedProduct);
        $this->mapChildData($product, $mappedProduct);
        $this->mapConfigurableData($product, $mappedProduct);
        $this->mapCategories($product, $mappedProduct);
        $this->mapImages($product, $mappedProduct);
        $this->mapParentProduct($product, $mappedProduct);

        $isValidURL = $this->isValidUrl($mappedProduct['url']);

        if ($mappedProduct['mainImage'] == "" ||
           empty($mappedProduct['categories']) ||
           empty($mappedProduct['sellingPrice']) ||
           $mappedProduct['sellingPrice'] == 0 ||
           ($this->isBrandMandatory && (!isset($mappedProduct) || empty($mappedProduct['brand']))) ||
           $isValidURL === false
        ) {
            $requiredData = [
               'Main Image' => $mappedProduct['mainImage'],
               'Categories Count' => count($mappedProduct['categories']),
               'Selling Price' => $mappedProduct['sellingPrice'],
            ];
            if ($this->isBrandMandatory) {
                $requiredData['Brand'] = isset($mappedProduct['brand']) ? $mappedProduct['brand'] : '';
            }
            if ($isValidURL === false) {
                $requiredData[SkippedEntityData::URL_KEY] = $mappedProduct['url'];
            }
            $requiredData = json_encode($requiredData);

            $this->output->log([
               'Message' => 'Product Skipped',
               'ID' => $product->getId(),
               'Reason' => "Don't have enough required details",
               'Data' => $requiredData
            ], IndexerOutput::LOG_INFO_TYPE);

            $this->skippedProducts[$mappedProduct['id']] = [
               'id' => $mappedProduct['id'],
               'data' => $requiredData,
            ];

            return null;
        }

        $this->mapReviews($product, $mappedProduct);
        $this->mapOrderData($product, $mappedProduct);
        $this->mapDiscounts($product, $mappedProduct);
        $this->mapAttributes($product, $mappedProduct, $mappedProduct['inStock']);
        $this->addParentDataInChild($mappedProduct);

        return $mappedProduct;
    }

    private function mapConfigurableData($product, &$mappedProduct)
    {
        $categories = $this->configurableProductsData->getProductCategories($product);
        $attributes = $this->configurableProductsData->getProductAttributes($product, $this->attributesToIgnore);

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
                $mappedProduct['inStock'] = false;
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
            'finalPrices' => [],
            ];

            $isAllChildOutOfStock = true;

            foreach ($children as $child) {
                $childFinalPrice = $this->productPrices->getFinalPrice($child);
                $childOriginalPrice = $this->productPrices->getOriginalPrice($child);
                $childSellingPrice = $this->productPrices->getSellingPrice($child);

                if ($finalPrice < $childFinalPrice) {
                    $finalPrice = $childFinalPrice;
                    $sellingPrice = $childSellingPrice;

                    if ($childSellingPrice < $childOriginalPrice) {
                        $discount = ($childOriginalPrice - $childSellingPrice);
                        $discountPercetnage =
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

                $stockItem = $this->stockRegistry->getStockItem($child->getId());
                if ($stockItem && $stockItem->getIsInStock()) {
                    $isAllChildOutOfStock = false;
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

                $categories = $this->configurableProductsData->getProductCategories($child);
                $attributes = $this->configurableProductsData->getProductAttributes($child, $this->attributesToIgnore);

                $childColors = $this->configurableProductsData->getColors($categories, $attributes, $this->storeId);
                $childSizes = $this->configurableProductsData->getSizes($categories, $attributes, $this->storeId);

                $variationInStock = ($stockItem && $stockItem->getIsInStock());
                $this->addProductVariationDetails($childColors, $child, $variationInStock);
                $this->addProductVariationDetails($childSizes, $child, $variationInStock);

                if (count($childColors)) {
                    array_push($colors, ...$childColors);
                }
                if (count($childSizes)) {
                    array_push($sizes, ...$childSizes);
                }

                $this->mapAttributes($child, $mappedProduct, $variationInStock, true);
            }

            if ($isAllChildOutOfStock) {
                $mappedProduct['inStock'] = false;
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
         'finalPrices' => 'finalPrice',
         'prices' => 'price',
         'discounts' => 'discount',
         'discountPercentages' => 'discountPercentage',
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
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $id = $attribute->getAttributeId();
            if (isset($this->attributesToIgnore[$id])) {
                continue;
            }

            $code = $attribute->getAttributeCode();
            $isUserDefined = $attribute->getIsUserDefined();
            $isFilterableInSearch = ($attribute->getIsFilterableInSearch()) ? true : false;
            $isSearchable = ($attribute->getIsSearchable()) ? true : false;
            $isFilterable = ($attribute->getIsFilterable()) ? true: false;

            $isSearchableOrFilterable = ($isFilterableInSearch || $isFilterable || $isSearchable);

            if (!$this->isSerachableFrontendInputType($attribute)) {
                $isSearchable = false;
            }

            if (($isUserDefined || $this->isSystemDefinedAttribute($attribute)) && $isSearchableOrFilterable) {
                $value = $this->getAttributeValue($product, $attribute);
                $label = $attribute->getFrontendLabel();

                if (count($value) === 0 || (count($value) == 1 && empty($value[0]))
                    || (count($value) == 1 && $value[0] === null)) {
                    continue;
                }

                $autocompleteConfig =
                   $this->getAutocompleteConfig($attribute, $autocompleteAttributes);
                $attributeValueToAdd = [
                'value' => $value,
                'inStock' => $variationInStock,
                'variationId' => $product->getId(),
                ];
                $swatch = $this->attributesManager->getSwatchDetails($product, $attribute);
                if ($swatch) {
                    $attributeValueToAdd['swatch'] = $swatch;
                }

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

    private function getReservedAttributeCodes(): array
    {
        return [
          'visibility',
          'name',
          'price',
          'description',
          'created_at',
          'updated_at',
          'category_ids',
          'status',
        ];
    }

    private function isSystemDefinedAttribute($attribute)
    {
        $isUserDefined = $attribute->getIsUserDefined();
        if (!$isUserDefined) {
            $code = $attribute->getAttributeCode();
            if ($code && !in_array($code, $this->getReservedAttributeCodes())) {
                return true;
            }
        }

        return false;
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
        $value = $this->productsAttributesManager->getValue($attribute->getId(), $product->getId());
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

    private function isValidUrl($url)
    {
        $url = str_replace(" ", "%20", $url);
        return (
         ($url !== "" && is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false) &&
         strpos($url, $this->adminUrl) === false
        );
    }

    private function mapParentProduct($product, &$mappedProduct)
    {
        $parentProductIds = $this->configurable->getParentIdsByChild($product->getId());
        if (count($parentProductIds)) {
            $parentProductId = array_shift($parentProductIds);
            $mappedProduct['groupId'] = $parentProductId;

            $parentProducts = $this->productsSessionStorage->getByIds([$parentProductId]);
            if (count($parentProducts)) {
                foreach ($parentProducts as $parentProduct) {
                    $mappedProduct['url'] = $this->productURLManager->getUrl($parentProduct);
                    $visibility = $parentProduct->getVisibility();
                    $mappedProduct['isSearchable'] = (
                       $visibility == Visibility::VISIBILITY_IN_SEARCH ||
                       $visibility == Visibility::VISIBILITY_BOTH) ? true : false;
                    $mappedProduct['isVisibleInCatalog'] = (
                       $visibility == Visibility::VISIBILITY_IN_CATALOG ||
                       $visibility == Visibility::VISIBILITY_BOTH) ? true : false;

                    if (!$mappedProduct['mainImage'] ||
                        $mappedProduct['mainImage'] ==
                            $this->productImageManager->getPlaceholderImage($this->storeId)) {
                        $this->mapImages($parentProduct, $mappedProduct);
                    }
                    break;
                }
            }
        }
    }

    private function getFloatVal($value)
    {
        if (!empty($value)) {
            return floatval(number_format($value, 2, '.', ''));
        }

        return $value;
    }

    private function getProductDescription($product)
    {
        if ($this->commonWordsToRemove === null) {
            $this->commonWordsToRemove = $this->storeCatalogueConfig->getCommonDescriptionWordsToRemove();
            $this->hasWordsToRemove = (count($this->commonWordsToRemove) > 0);
        }
        $description = $product->getDescription();
        if ($this->hasWordsToRemove) {
            $description = str_replace($this->commonWordsToRemove, "", $description);
        }

        return $description;
    }

    private function mapBasicDetails($product, &$mappedProduct)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        $visibility = $product->getVisibility();
        $finalPrice = $this->productPrices->getFinalPrice($product);

        $sellingPrice = $this->getFloatVal($this->productPrices->getSellingPrice($product));
        $sellingPriceWithoutTax = $this->getFloatVal($finalPrice);

        $mappedProduct = [
         'id' => $product->getId(),
         'name' => $product->getName(),
         'sellingPrice' => $sellingPrice,
         'finalPrice' => $sellingPriceWithoutTax,
         'description' => $this->getProductDescription($product),
         'url' => $this->productURLManager->getUrl($product),
         'inStock' => ($stockItem && $stockItem->getIsInStock()),
         'stockQty' => ($stockItem && $stockItem->getQty() > 0) ? $stockItem->getQty() : 0,
         'isSearchable' => (
            $visibility == Visibility::VISIBILITY_IN_SEARCH ||
            $visibility == Visibility::VISIBILITY_BOTH) ? true : false,
         'isVisibleInCatalog' => (
            $visibility == Visibility::VISIBILITY_IN_CATALOG ||
            $visibility == Visibility::VISIBILITY_BOTH) ? true : false,
        ];

        $createdAt = $product->getCreatedAt();
        $updatedAt = $product->getUpdatedAt();

        if ($createdAt != "0000-00-00 00:00:00") {
            $mappedProduct['createdAt'] = $createdAt;
        }

        if ($updatedAt != "0000-00-00 00:00:00") {
            $mappedProduct['updatedAt'] = $updatedAt;
        }

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
            $mappedProduct['noOfTimesOrdered'] = $summary['orders'];
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

    private function getCategories($product)
    {
        $categories = $this->configurableProductsData->getProductCategories($product);
        $categoriesAssoc = [];

        foreach ($categories as $category) {
            $categoryToAdd = $this->getCategoryArrayToSend($category);
            if ($category['isActive']) {
                $categoriesAssoc[$category['id']] = $categoryToAdd;
            }
        }
        if (count($categoriesAssoc) === 0) {
            $defaultCategory = $this->configurableProductsData->getDefaultUnassignedCategory($this->storeId);
            if ($defaultCategory) {
                return [$this->getCategoryArrayToSend($defaultCategory)];
            }
        }

        return array_values($categoriesAssoc);
    }

    private function getCategoryArrayToSend($category)
    {
        return [
         'name' => $category['name'],
         'id'  =>
            (!empty($category['urlKey']) && $category['urlKey'] != null)
               ? $category['urlKey'] : $category['id'],
         'url' => $category['url'],
         'position' => (int) $category['position'],
         'level'  => (int) $category['level'],
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
        $thumbnail = $product->getThumbnail();
        $mainImageUrl = "";

        foreach ($product->getMediaGalleryImages() as $productImage) {
            $productImageData = $productImage->getData();
            if ($productImageData['disabled'] == 1) {
                continue;
            }

            if ($index == 0 || $thumbnail == $productImageData['file']) {
                $mainImage = $this->productImageManager->getThumbnail($product, $productImageData['file']);
                $mainImageUrl = $productImageData['url'];
            } else {
                $images[] = $productImageData['url'];
            }
            $index++;
        }

        if ($mainImage === '') {
            if ($mainImageUrl !== "") {
                $mainImage = $mainImageUrl;
            } else {
                $mainImage = $this->productImageManager->getPlaceholderImage($this->storeId);
            }
        }

        return [
         'images' => $images,
         'mainImage' => $mainImage,
        ];
    }
}
