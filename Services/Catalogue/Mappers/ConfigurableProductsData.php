<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Magento\Framework\DataObject;
use Wizzy\Search\Services\Catalogue\AttributesManager;
use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Catalogue\Configurables\BrandConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\ColorConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\GenderConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\SizeConfigurable;
use Wizzy\Search\Services\Catalogue\ProductsAttributesManager;
use Wizzy\Search\Services\Queue\SessionStorage\CategoriesSessionStorage;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Magento\Framework\Event\ManagerInterface;

class ConfigurableProductsData
{
    private $categoriesToIgnoreInAutoComplete;
    private $hasToIgnoreCategories;
    private $brandConfigurable;
    private $genderConfigurable;
    private $colorConfigurable;
    private $sizeConfigurable;

    private $storeAutocompleteConfig;

    private $categoriesManager;
    private $attributesManager;

    private $autocompleteAttributes;
    private $categoriesSessionStorage;
    private $rootCategory;

    private $productsAttributesManager;

    private $eventManager;

    public function __construct(
        ManagerInterface $eventManager,
        BrandConfigurable $brandConfigurable,
        CategoriesManager $categoriesManager,
        GenderConfigurable $genderConfigurable,
        ColorConfigurable $colorConfigurable,
        SizeConfigurable $sizeConfigurable,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        AttributesManager $attributesManager,
        CategoriesSessionStorage $categoriesSessionStorage,
        ProductsAttributesManager $productsAttributesManager
    ) {
        $this->eventManager = $eventManager;
        $this->brandConfigurable = $brandConfigurable;
        $this->genderConfigurable = $genderConfigurable;
        $this->colorConfigurable = $colorConfigurable;
        $this->sizeConfigurable = $sizeConfigurable;
        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->categoriesManager = $categoriesManager;
        $this->attributesManager = $attributesManager;
        $this->categoriesSessionStorage = $categoriesSessionStorage;
        $this->autocompleteAttributes = [];
        $this->rootCategory = false;
        $this->productsAttributesManager = $productsAttributesManager;
        $this->hasToIgnoreCategories = $this->storeAutocompleteConfig->hasToIgnoreCategories();
        $this->categoriesToIgnoreInAutoComplete = $this->storeAutocompleteConfig->getIgnoredCategories();
    }

    public function getBrand($categories, $attributes, $storeId)
    {
        return $this->brandConfigurable->getValue($categories, $attributes, $storeId);
    }

    public function getGender($categories, $attributes, $storeId)
    {
        return $this->genderConfigurable->getValue($categories, $attributes, $storeId);
    }

    public function getColors($categories, $attributes, $storeId)
    {
        return $this->colorConfigurable->getValue($categories, $attributes, $storeId);
    }

    public function getSizes($categories, $attributes, $storeId)
    {
        return $this->sizeConfigurable->getValue($categories, $attributes, $storeId);
    }

    public function getAutocompleteAttributes($storeId)
    {
        if (isset($this->autocompleteAttributes[$storeId])) {
            return $this->autocompleteAttributes[$storeId];
        }
        $this->storeAutocompleteConfig->setStore($storeId);
        $attributes = $this->storeAutocompleteConfig->getAttributes();
        if (!$attributes) {
            return [];
        }
        $attributes = json_decode($attributes, true);

        $attributesToReturn = [];
        foreach ($attributes as $key => $attributeValues) {
            $attributesToReturn[$attributeValues['attribute']] = [
            'id' => $attributeValues['attribute'],
            'position' => $attributeValues['position'],
            'glue' => $attributeValues['autocomplete_glue'],
            ];
        }

        $this->autocompleteAttributes[$storeId] = $attributesToReturn;

        return $attributesToReturn;
    }

    public function getAttributesToIgnore($storeId)
    {
        $brandAttributes = $this->brandConfigurable->getConfiguredAttributes($storeId);
        $genderAttributes = array_keys($this->genderConfigurable->getConfiguredAttributes($storeId));
        $colorAttributes = array_column($this->colorConfigurable->getConfiguredAttributes($storeId), 'value');
        $sizeAttributes = array_column($this->sizeConfigurable->getConfiguredAttributes($storeId), 'value');

        $attributesToIgnore = array_unique(
            array_merge(
                $brandAttributes,
                $genderAttributes,
                $colorAttributes,
                $sizeAttributes
            )
        );

        return $attributesToIgnore;
    }

    public function getCategoriesToIgnore($storeId)
    {
        $brandCategories = $this->brandConfigurable->getConfiguredCategories($storeId);
        $genderCategories = array_column($this->genderConfigurable->getConfiguredCategories($storeId), 'id');
        $colorCategories = $this->colorConfigurable->getConfiguredCategories($storeId);
        $sizeCategories = $this->sizeConfigurable->getConfiguredCategories($storeId);

        $categoriesToIgnore = array_unique(
            array_merge(
                $brandCategories,
                $genderCategories,
                $colorCategories,
                $sizeCategories
            )
        );

        return $categoriesToIgnore;
    }

    public function getProductAttributes($product, $attributesToConsider)
    {
        $attributes = [];
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {

            $value = $this->productsAttributesManager->getValue($attribute->getId(), $product->getId());
            if (!isset($attributesToConsider[$attribute->getId()]) || $value === null) {
                continue;
            }

            $attributeArr = [
            'id' => $attribute->getId(),
            'value' => $value,
            ];

            $swatch = $this->attributesManager->getSwatchDetails($product, $attribute);
            if ($swatch) {
                $attributeArr['swatch'] = $swatch;
            }

            $attributes[$attribute->getId()] = $attributeArr;
        }

        return $attributes;
    }

    /**
     * Get default unassigned category
     * @param $storeId
     * @return array|false|null
     */
    public function getDefaultUnassignedCategory($storeId)
    {
        if ($this->rootCategory !== false) {
            return $this->rootCategory;
        }

        $this->rootCategory = null;

        $category = $this->categoriesManager->getRootCategory($storeId);
        if ($category) {
            $this->rootCategory =  $this->getCategoryArray($category);
        }

        $this->rootCategory['isSearchable'] = false;
        $this->rootCategory['includeInMenu'] = false;

        return $this->rootCategory;
    }

    public function getProductCategories($product)
    {
        $categoryIds = $product->getCategoryIds();
        $categories = $this->categoriesSessionStorage->getByIds($categoryIds);
        $categoriesAssoc = [];

        foreach ($categories as $category) {
            $parentCategories = $this->categoriesManager->getParentIdsOfCategory($category);
            if (!count($parentCategories)) {
                continue;
            }
            $parentCategories = $this->categoriesSessionStorage->getByIds($parentCategories);

            foreach ($parentCategories as $parentCategory) {
                if (!isset($categoriesAssoc[$parentCategory->getId()])) {
                    $parentCategoryArr = $this->getCategoryArray($parentCategory);
                    $parentCategoryArr['isParent'] = true;
                    $categoriesAssoc[$parentCategory->getId()] = $parentCategoryArr;
                }
            }

            $categoryArr = $this->getCategoryArray($category);
            $categoriesAssoc[$category->getId()] = $categoryArr;
        }

        $dataObject = new DataObject([
            'categories' => $categoriesAssoc,
        ]);
        $this->eventManager->dispatch(
            'wizzy_after_product_categories_generated',
            ['data' => $dataObject, 'product' => $product]
        );
        return $dataObject->getDataByKey('categories');
    }

    private function getCategoryArray($category)
    {
        $pathIds = $category->getPathIds();
        if (count($pathIds)) {
            unset($pathIds[0]);
            $pathIds = array_values($pathIds);
            $pathCategories = $this->categoriesSessionStorage->getByIds($pathIds);

            $pathIds = [];
            foreach ($pathCategories as $pathCategory) {
                $pathIds[] = $pathCategory->getUrlKey();
            }
        }

        if (!count($pathIds)) {
            $pathIds = [$category->getUrlKey()];
        }

        $parentId = '';
        try {
            $parentId = ($category->getParentCategory()) ? $category->getParentCategory()->getId() : '';
        } catch (\Exception $e) {
            $parentId = '';
        }

        $parentUrlKey = '';
        try {
            $parentUrlKey = ($category->getParentCategory()) ? $category->getParentCategory()->getUrlKey() : '';
        } catch (\Exception $e) {
            $parentUrlKey = '';
        }

        $data =
         ['id' => $category->getId(),
         'value' => $category->getName(),
         'name' => $category->getName(),
         'isParent' => false,
         'urlKey' => $category->getUrlKey(),
         'position' => (int) $category->getPosition(),
         'level'  => (int) $category->getLevel(),
         'description' => ($category->getDescription()) ? $category->getDescription() : '',
         'image' => ($category->getImageUrl()) ? $category->getImageUrl() : '',
         'url' => $category->getUrl(),
         'isActive'=> $category->getIsActive(),
         'pathIds' => $pathIds,
         'parentId' => $parentId,
         'parentUrlKey' => $parentUrlKey,
        ];

        $includeInMenu = ($category->getIncludeInMenu()) ? true : false;
        $isSearchable = (!$category->getIncludeInMenu() || !$category->getIsActive()) ? false : true;
        
        if ($this->hasToIgnoreCategories && in_array($category->getId(), $this->categoriesToIgnoreInAutoComplete)) {
            $includeInMenu = false;
            $isSearchable = false;
        }

        $data['includeInMenu'] = $includeInMenu;
        $data['isSearchable'] = $isSearchable;
        
        return $data;
    }
}
