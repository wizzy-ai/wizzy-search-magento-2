<?php

namespace Wizzy\Search\Services\Catalogue;

class ProductsAttributesManager
{
    private $values;

    public function __construct()
    {
        $this->values = [];
    }

    public function setAttributeValues(array $products)
    {
        $this->values = [];
        foreach ($products as $product) {
            $attributes = $product->getAttributes();
            foreach ($attributes as $attribute) {
                $value = $attribute->getFrontend()->getValue($product);
                $this->setValue($attribute->getId(), $product->getId(), $value);
            }
        }
    }

    private function setValue($attributeId, $productId, $value)
    {
        if (!isset($this->values[$productId])) {
            $this->values[$productId] = [];
        }

        $this->values[$productId][$attributeId] = $value;
    }

    public function getValue($attributeId, $productId)
    {
        if (isset($this->values[$productId]) && isset($this->values[$productId][$attributeId])) {
            return $this->values[$productId][$attributeId];
        }

        return null;
    }
}
