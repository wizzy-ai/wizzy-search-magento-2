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

    /**
     * Cache primary products (including configurable children) and direct values
     * for any supplemental products.
     */
    public function setAttributeValues(array $products, array $supplementalProducts = [])
    {
        $this->values = [];
        $primaryProductIds = [];

        foreach ($products as $product) {
            $primaryProductIds[$product->getId()] = true;
            $this->setProductAttributeValues($product);

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
                        if (isset($this->values[$childId]) &&
                            array_key_exists($attributeId, $this->values[$childId])) {
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

        $supplementalProductIds = [];
        foreach ($supplementalProducts as $product) {
            $productId = $product->getId();
            if (isset($primaryProductIds[$productId]) || isset($supplementalProductIds[$productId])) {
                continue;
            }

            $supplementalProductIds[$productId] = true;
            $this->setProductAttributeValues($product);
        }
    }

    private function setProductAttributeValues($product)
    {
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
            $value = $attribute->getFrontend()->getValue($product);
            if (empty($value)) {
                $value = $product->getData($attribute->getAttributeCode());
            }
            $this->setValue($attribute->getId(), $product->getId(), $value);
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
