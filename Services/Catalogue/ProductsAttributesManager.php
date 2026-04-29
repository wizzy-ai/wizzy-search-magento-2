<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

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
                if (empty($value)) {
                    $value = $product->getData($attribute->getAttributeCode());
                }
                $this->setValue($attribute->getId(), $product->getId(), $value);
            }

            if ($product->getTypeID() == Configurable::TYPE_CODE) {
                $children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($children as $child) {
                    $childId = $child->getId();
                    $childAttributes = $child->getAttributes();

                    foreach ($childAttributes as $childAttribute) {
                        $attributeId = $childAttribute->getId();
                        // Don't overwrite a value already stored for this child by the
                        // top-level loop. The top-level loop processes the fully-loaded
                        // product object (with the getData() fallback below); the child
                        // object returned by getUsedProducts() is lightweight and may
                        // resolve missing attribute data to a default (e.g. a Yes/No
                        // attribute coerces null to "No"), which would silently replace
                        // the child's real value.
                        if (isset($this->values[$childId][$attributeId])) {
                            continue;
                        }
                        $value = $childAttribute->getFrontend()->getValue($child);
                        if (empty($value)) {
                            $value = $child->getData($childAttribute->getAttributeCode());
                        }
                        $this->setValue($attributeId, $childId, $value);
                    }
                }
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
