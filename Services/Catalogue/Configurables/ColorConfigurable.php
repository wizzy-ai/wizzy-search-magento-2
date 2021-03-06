<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

use Wizzy\Search\Services\Store\StoreCatalogueConfig;

class ColorConfigurable implements ConfigurableImplInterface
{

    private $storeCatalogueConfig;
    private $configuredAttributes;

    public function __construct(StoreCatalogueConfig $storeCatalogueConfig)
    {
        $this->storeCatalogueConfig = $storeCatalogueConfig;
        $this->configuredAttributes = [];
    }

    public function getValue(array $categories, array $attributes, $storeId)
    {
        $this->storeCatalogueConfig->setStore($storeId);
        $configuredAttributes = $this->getConfiguredAttributes($storeId);

        $colors = [];

        foreach ($configuredAttributes as $attributeId => $configuredAttribute) {
            if (isset($attributes[$attributeId]) && $attributes[$attributeId]['value']) {
                 $colorsArr = [
                'id'    => $attributeId,
                'value' => $attributes[$attributeId]['value'],
                'type'  => 'attribute',
                 ];

                 if (isset($attributes[$attributeId]['swatch'])) {
                     $colorsArr['swatch'] = $attributes[$attributeId]['swatch'];
                 }
                 $colors[$attributeId] = $colorsArr;
            }
        }

        return $colors;
    }

    public function getConfiguredCategories($storeId)
    {
        return [];
    }

    public function getConfiguredAttributes($storeId)
    {
        if (isset($this->configuredAttributes[$storeId])) {
            return $this->configuredAttributes[$storeId];
        }
        $this->storeCatalogueConfig->setStore($storeId);
        $attributes = [];

        if ($this->storeCatalogueConfig->hasColorVariableProducts()) {
            $mappedAttributes = $this->storeCatalogueConfig->colorIdentityAttributes();
            if ($mappedAttributes) {
                $mappedAttributes = explode(",", $mappedAttributes);

                foreach ($mappedAttributes as $mappedAttribute) {
                    $attributes[$mappedAttribute] = [
                    'value' => $mappedAttribute,
                    ];
                }
            }
        }

        $this->configuredAttributes = $attributes;
        return $attributes;
    }
}
