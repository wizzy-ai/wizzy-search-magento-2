<?php

namespace Wizzy\Search\Services\Catalogue\Configurables;

use Wizzy\Search\Services\Store\StoreCatalogueConfig;

class SizeConfigurable implements ConfigurableImplInterface
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

        $sizes = [];

        foreach ($configuredAttributes as $attributeId => $configuredAttribute) {
            if (isset($attributes[$attributeId]) && $attributes[$attributeId]['value']) {
                 $sizeArr = [
                'id'    => $attributeId,
                'value' => $attributes[$attributeId]['value'],
                'type'  => 'attribute',
                 ];

                 if (isset($attributes[$attributeId]['swatch'])) {
                     $sizeArr['swatch'] = $attributes[$attributeId]['swatch'];
                 }

                 $sizes[$attributeId] = $sizeArr;
            }
        }

        return $sizes;
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

        if ($this->storeCatalogueConfig->hasSizeVariableProducts()) {
            $mappedAttributes = $this->storeCatalogueConfig->sizeIdentityAttributes();
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
