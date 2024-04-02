<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\AttributesManager;

class ExtraAttributesList
{
    private $attributesManager;

    public function __construct(AttributesManager $attributesManager)
    {
        $this->attributesManager = $attributesManager;
    }

    public function toOptionArray()
    {
        $attributes = $this->attributesManager->getExtraAttributes();
        foreach ($attributes as $attribute) {
            if ($attribute->getEntityType() &&
                $attribute->getEntityType()->getEntityTypeCode() == "catalog_product"
            ) {
                $options[] = [
                    'value' => $attribute->getId(),
                    'label' => $attribute->getStoreLabel() . " (" . $attribute->getName() . ")",
                ];
            }
        }

        return $options;
    }
}
