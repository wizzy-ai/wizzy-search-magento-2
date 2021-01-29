<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Swatches\Helper\Data;
use Wizzy\Search\Services\Store\StoreManager;

class AttributesManager
{

    private $attributesCollection;
    private $storeManager;
    private $swatchHelper;

    public function __construct(CollectionFactory $attributesCollection, StoreManager $storeManager, Data $swatchHelper)
    {
        $this->attributesCollection = $attributesCollection;
        $this->storeManager = $storeManager;
        $this->swatchHelper = $swatchHelper;
    }

    public function fetchAll()
    {
        $attributes = $this->attributesCollection->create();
        $attributes = $attributes->setOrder('frontend_label', 'ASC');
        $attributes = $attributes->addFilter('is_user_defined', '1');
        $attributes = $attributes->addFieldToFilter(
            ['is_filterable', 'is_filterable', 'is_filterable_in_search'],
            [1, [1, 2], 1]
        );

        $attributesToReturn = [];

        foreach ($attributes as $attribute) {
            if ($attribute->getEntityType() && $attribute->getEntityType()->getEntityTypeCode() == "catalog_product") {
                $attributesToReturn[] = $attribute;
            }
        }

        return $attributesToReturn;
    }

    private function getSwatchValues($optionIds)
    {
        $swatchValues = $this->swatchHelper->getSwatchesByOptionsId($optionIds);
        if (count($swatchValues)) {
            $swatchValues = array_values($swatchValues);
            return $swatchValues[0]['value'];
        }
        return null;
    }

    private function getSwatchType($attribute)
    {
        if ($this->swatchHelper->isTextSwatch($attribute)) {
            return "text";
        }

        if ($this->swatchHelper->isVisualSwatch($attribute)) {
            return "visual";
        }

        return "";
    }

    public function getSwatchDetails($product, $attribute)
    {
        $swatchType = $this->getSwatchType($attribute);
        if ($swatchType) {
            $value = $attribute->getFrontend()->getValue($product);
            $optionIds = [];
            if (is_string($value)) {
                $optionIds = [$attribute->getSource()->getOptionId($value)];
            }

            $swatchValue = $this->getSwatchValues($optionIds);
            if (count($optionIds) && $swatchValue !== null) {
                return [
                 'type' => $swatchType,
                 'value' => $swatchValue,
                ];
            }
        }

        return null;
    }
}
