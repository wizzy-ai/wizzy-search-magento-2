<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

use Wizzy\Search\Model\Admin\Source\IdentityValueBy;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;

class GenderConfigurable implements ConfigurableImpl {

  private $storeCatalogueConfig;

  public function __construct(StoreCatalogueConfig $storeCatalogueConfig) {
    $this->storeCatalogueConfig = $storeCatalogueConfig;
  }

  public function getValue(array $categories, array $attributes, $storeId) {
    $this->storeCatalogueConfig->setStore($storeId);

    $configuredCategories = $this->getConfiguredCategories($storeId);
    $configuredAttributes = $this->getConfiguredAttributes($storeId);
    $considerParentCategories = $this->storeCatalogueConfig->genderIdentityConsiderParentCategories();

    foreach ($configuredCategories as $categoryId => $configuredCategory) {
      if (isset($categories[$categoryId]) && (!$categories[$categoryId]['isParent']  || ($categories[$categoryId]['isParent'] && $considerParentCategories) )) {
        return [
           'id' => $categoryId,
           'value' => $configuredCategory['gender'],
           'type' => 'category'
        ];
      }
    }

    foreach ($configuredAttributes as $attributeId => $configuredAttribute) {
      if (isset($attributes[$attributeId])) {
        $attributeValue = strtolower($attributes[$attributeId]['value']);
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

  public function getConfiguredCategories($storeId) {
     $this->storeCatalogueConfig->setStore($storeId);
    $categories = [];

    if ($this->storeCatalogueConfig->isMultiGenderStore()) {
      if ($this->storeCatalogueConfig->identifyGenderBy() == IdentityValueBy::CATEGORIES) {
        $categoriesMapping = $this->storeCatalogueConfig->genderIdentityCategories();
        if ($categoriesMapping) {
          $categoriesMapping = json_decode($categoriesMapping, TRUE);

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

    return $categories;
  }

  public function getConfiguredAttributes($storeId) {
     $this->storeCatalogueConfig->setStore($storeId);
    $attributes = [];

    if ($this->storeCatalogueConfig->isMultiGenderStore()) {
      if ($this->storeCatalogueConfig->identifyGenderBy() == IdentityValueBy::ATTRIBUTES) {
        $attributesMapping = $this->storeCatalogueConfig->genderIdentityAttributes();
        if ($attributesMapping) {
          $attributesMapping = json_decode($attributesMapping, TRUE);

          foreach ($attributesMapping as $attributeMapping) {

            if (!isset($attributes[$attributeMapping['attribute']])) {
              $attributes[$attributeMapping['attribute']] = array();
            }

            $attributes[$attributeMapping['attribute']][$attributeMapping['attribute_value']] = [
              'id'     => $attributeMapping['attribute'],
              'gender' => strtolower($attributeMapping['gender']),
            ];

          }

        }
      }
    }

    return $attributes;
  }
}