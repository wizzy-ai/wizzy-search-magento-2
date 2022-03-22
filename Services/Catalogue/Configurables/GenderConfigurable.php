<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

use Wizzy\Search\Model\Admin\Source\IdentityValueBy;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;

class GenderConfigurable implements ConfigurableImplInterface
{

    private $storeCatalogueConfig;

    private $configuredCategories;
    private $configuredAttributes;

    public function __construct(StoreCatalogueConfig $storeCatalogueConfig)
    {
        $this->storeCatalogueConfig = $storeCatalogueConfig;

        $this->configuredCategories = [];
        $this->configuredAttributes = [];
    }

    public function getValue(array $categories, array $attributes, $storeId)
    {
        $this->storeCatalogueConfig->setStore($storeId);

        $configuredCategories = $this->getConfiguredCategories($storeId);
        $configuredAttributes = $this->getConfiguredAttributes($storeId);
        $considerParentCategories = $this->storeCatalogueConfig->genderIdentityConsiderParentCategories();

        foreach ($configuredCategories as $categoryId => $configuredCategory) {
            if (isset($categories[$categoryId]) &&
               (!$categories[$categoryId]['isParent'] ||
                  ($categories[$categoryId]['isParent'] && $considerParentCategories)
               )
            ) {
                return [
                 'id' => $categoryId,
                 'value' => $configuredCategory['gender'],
                 'type' => 'category'
                ];
            }
        }

        foreach ($configuredAttributes as $attributeId => $configuredAttribute) {
            if (isset($attributes[$attributeId])) {
                $attributeValue = $attributes[$attributeId]['value'];
                if (isset($configuredAttribute[$attributeValue])) {
                     return [
                      'id' => $attributeId,
                      'value' => $configuredAttribute[$attributeValue]['gender'],
                      'type' => 'attribute'
                     ];
                }
            }
        }

        return '';
    }

    public function getConfiguredCategories($storeId)
    {
        if (isset($this->configuredCategories[$storeId])) {
            return $this->configuredCategories[$storeId];
        }
        $this->storeCatalogueConfig->setStore($storeId);
        $categories = [];

        if ($this->storeCatalogueConfig->isMultiGenderStore()) {
            if ($this->storeCatalogueConfig->identifyGenderBy() == IdentityValueBy::CATEGORIES) {
                $categoriesMapping = $this->storeCatalogueConfig->genderIdentityCategories();
                if ($categoriesMapping) {
                    $categoriesMapping = json_decode($categoriesMapping, true);

                    foreach ($categoriesMapping as $categoryMapping) {
                        $mappedCategories = $categoryMapping['categories'];
                        foreach ($mappedCategories as $mappedCategory) {
                            $categories[$mappedCategory] = [
                            'id'     => $mappedCategory,
                            'gender' => $categoryMapping['gender']
                            ];
                        }
                    }

                }
            }
        }

        $this->configuredCategories = $categories;
        return $categories;
    }

    public function getConfiguredAttributes($storeId)
    {
        if (isset($this->configuredAttributes[$storeId])) {
            return $this->configuredAttributes[$storeId];
        }
        $this->storeCatalogueConfig->setStore($storeId);
        $attributes = [];

        if ($this->storeCatalogueConfig->isMultiGenderStore()) {
            if ($this->storeCatalogueConfig->identifyGenderBy() == IdentityValueBy::ATTRIBUTES) {
                $attributesMapping = $this->storeCatalogueConfig->genderIdentityAttributes();
                if ($attributesMapping) {
                    $attributesMapping = json_decode($attributesMapping, true);

                    foreach ($attributesMapping as $attributeMapping) {

                        if (!isset($attributes[$attributeMapping['attribute']])) {
                            $attributes[$attributeMapping['attribute']] = [];
                        }

                        $attributes[$attributeMapping['attribute']][$attributeMapping['attribute_value']] = [
                        'id'     => $attributeMapping['attribute'],
                        'gender' => $attributeMapping['gender'],
                        ];

                    }

                }
            }
        }

        $this->configuredAttributes = $attributes;
        return $attributes;
    }
}
