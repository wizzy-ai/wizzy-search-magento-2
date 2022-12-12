<?php

namespace Wizzy\Search\Model\Admin\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class AllAttributesList
{
    protected $_attributeFactory;

    public function __construct(CollectionFactory $attributesCollection)
    {
        $this->attributesCollection = $attributesCollection;
    }

    public function toOptionArray()
    {
        $attributes = $this->attributesCollection->create();

        foreach ($attributes as $attribute) {
            if ($attribute->getEntityType() && $attribute->getEntityType()->getEntityTypeCode() == "catalog_product") {
                $options[] = [
                    'value' => $attribute->getData('attribute_code'),
                    'label' => $attribute->getData('frontend_label')
                    . " (" . $attribute->getData('attribute_code') . ")",
                ];
            }
        }
        return $options;
    }
}
