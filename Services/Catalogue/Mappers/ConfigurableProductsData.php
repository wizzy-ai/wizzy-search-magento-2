<?php

namespace Wizzy\Search\Services\Catalogue\Mappers;

use Wizzy\Search\Services\Catalogue\AttributesManager;
use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Catalogue\Configurables\BrandConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\ColorConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\GenderConfigurable;
use Wizzy\Search\Services\Catalogue\Configurables\SizeConfigurable;
use Wizzy\Search\Services\Queue\SessionStorage\CategoriesSessionStorage;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;

class ConfigurableProductsData
{

    private $brandConfigurable;
    private $genderConfigurable;
    private $colorConfigurable;
    private $sizeConfigurable;

    private $storeAutocompleteConfig;

    private $categoriesManager;
    private $attributesManager;

    private $autocompleteAttributes;
    private $categoriesSessionStorage;

    public function __construct(
        BrandConfigurable $brandConfigurable,
        CategoriesManager $categoriesManager,
        GenderConfigurable $genderConfigurable,
        ColorConfigurable $colorConfigurable,
        SizeConfigurable $sizeConfigurable,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        AttributesManager $attributesManager,
        CategoriesSessionStorage $categoriesSessionStorage
    ) {
        $this->brandConfigurable = $brandConfigurable;
        $this->genderConfigurable = $genderConfigurable;
        $this->colorConfigurable = $colorConfigurable;
        $this->sizeConfigurable = $sizeConfigurable;
        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->categoriesManager = $categoriesManager;
        $this->attributesManager = $attributesManager;
        $this->categoriesSessionStorage = $categoriesSessionStorage;
        $this->autocompleteAttributes = [];
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
        $genderAttributes = array_column($this->genderConfigurable->getConfiguredAttributes($storeId), 'id');
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

            if (!isset($attributesToConsider[$attribute->getId()])) {
                continue;
            }

            $attributeArr = [
            'id' => $attribute->getId(),
            'value' => $attribute->getFrontend()->getValue($product),
            ];

            $swatch = $this->attributesManager->getSwatchDetails($product, $attribute);
            if ($swatch) {
                $attributeArr['swatch'] = $swatch;
            }

            $attributes[$attribute->getId()] = $attributeArr;
        }

        return $attributes;
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

            $hasOneParentInMenu = false;

            foreach ($parentCategories as $parentCategory) {
                if (!isset($categoriesAssoc[$parentCategory->getId()])) {
                    $parentCategoryArr = $this->getCategoryArray($parentCategory);
                    $parentCategoryArr['isParent'] = true;
                    $categoriesAssoc[$parentCategory->getId()] = $parentCategoryArr;

                    $hasOneParentInMenu = $parentCategoryArr['includeInMenu'];
                }
            }

            $categoryArr = $this->getCategoryArray($category);

            $categoryArr['includeInMenu'] = ($categoryArr['includeInMenu'] && $hasOneParentInMenu);

            $categoriesAssoc[$category->getId()] = $categoryArr;
        }

        return $categoriesAssoc;
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
        return [
         'id' => $category->getId(),
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
         'includeInMenu' => ($category->getIncludeInMenu()) ? true : false,
         'pathIds' => $pathIds,
         'isSearchable' => (!$category->getIncludeInMenu() || !$category->getIsActive()) ? false : true,
         'parentId' => ($category->getParentCategory()) ? $category->getParentCategory()->getId() : '',
         'parentUrlKey' => ($category->getParentCategory()) ? $category->getParentCategory()->getUrlKey() : '',
        ];
    }
}
