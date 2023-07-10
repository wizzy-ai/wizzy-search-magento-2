<?php

namespace Wizzy\Search\Model\Admin\Source;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class AllAttributesList
{
    protected $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $attributes = $this->collectionFactory->create();
        $options = [];
        foreach ($attributes as $attribute) {
            if ($attribute->getEntityType() && $attribute->getEntityType()->getEntityTypeCode() == "catalog_product") {
                $options[] = [
                    'value' => $attribute->getData('attribute_code'),
                    'label' => $attribute->getData('frontend_label') . " (" .
                    $attribute->getData('attribute_code') . ")",
                ];
            }
        }
        return $options;
    }
}
