<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

use Wizzy\Search\Model\Admin\Source\IdentityCategoriesBy;
use Wizzy\Search\Model\Admin\Source\IdentityValueBy;
use Wizzy\Search\Services\Catalogue\CategoriesManager;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;

class BrandConfigurable implements ConfigurableImplInterface
{

    private $storeCatalogueConfig;
    private $categoriesManager;

    private $configuredCategories;
    private $configuredAttributes;

    public function __construct(StoreCatalogueConfig $storeCatalogueConfig, CategoriesManager $categoriesManager)
    {
        $this->storeCatalogueConfig = $storeCatalogueConfig;
        $this->categoriesManager = $categoriesManager;

        $this->configuredCategories = [];
        $this->configuredAttributes = [];
    }

    public function getValue(array $categories, array $attributes, $storeId)
    {
        $this->storeCatalogueConfig->setStore($storeId);

        $configuredCategories = $this->getConfiguredCategories($storeId);
        $configuredAttributes = $this->getConfiguredAttributes($storeId);

        foreach ($configuredCategories as $configuredCategory) {
            if (isset($categories[$configuredCategory]) && $categories[$configuredCategory]['value']) {
                return [
                 'type'  => 'category',
                 'id'    => $categories[$configuredCategory]['id'],
                 'value' => $categories[$configuredCategory]['value']
                ];
            }
        }

        foreach ($configuredAttributes as $configuredAttribute) {
            if (isset($attributes[$configuredAttribute]) && $attributes[$configuredAttribute]['value']) {
                return [
                'type'  => 'attribute',
                'id'    => $attributes[$configuredAttribute]['id'],
                'value' => $attributes[$configuredAttribute]['value']
                ];
            }
        }

        return null;
    }

    public function getConfiguredCategories($storeId)
    {
        if (isset($this->configuredCategories[$storeId])) {
            return $this->configuredCategories[$storeId];
        }
        $this->storeCatalogueConfig->setStore($storeId);
        $categories = [];

        if ($this->storeCatalogueConfig->isMultiBrandStore()) {
            if ($this->storeCatalogueConfig->idetifyBrandBy() == IdentityValueBy::CATEGORIES) {

                if ($this->storeCatalogueConfig->brandsIdentityCategoriesWay() == IdentityCategoriesBy::LEVEL) {
                    $categoryLevel = $this->storeCatalogueConfig->brandsIdentityCategoriesLevel();
                    $categories = $this->categoriesManager->fetchAllByLevel($categoryLevel, $storeId);
                    $categories = $this->extractCategoryIds($categories);
                }

                if ($this->storeCatalogueConfig->brandsIdentityCategoriesWay() ==
                   IdentityCategoriesBy::CATEGORIES_LIST) {
                    $categories = $this->storeCatalogueConfig->brandsIdentityCategories();
                    $categories = explode(",", $categories);
                }

            }
        }

        $this->configuredCategories[$storeId] = $categories;

        return $categories;
    }

    public function getConfiguredAttributes($storeId)
    {
        if (isset($this->configuredAttributes[$storeId])) {
            return $this->configuredAttributes[$storeId];
        }
        $this->storeCatalogueConfig->setStore($storeId);
        $attributes = [];

        if ($this->storeCatalogueConfig->isMultiBrandStore()) {
            if ($this->storeCatalogueConfig->idetifyBrandBy() == IdentityValueBy::ATTRIBUTES) {
                $attribute = $this->storeCatalogueConfig->brandsIdentityAttributes();
                if ($attribute) {
                    $attributes[] = $attribute;
                }
            }
        }

        $this->configuredAttributes[$storeId] = $attributes;

        return $attributes;
    }

    private function extractCategoryIds($categories)
    {
        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }
}
