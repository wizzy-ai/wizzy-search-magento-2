<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\AttributesManager;

class AttributesListNameKey
{
    private $attributesManager;

    public function __construct(AttributesManager $attributesManager)
    {
        $this->attributesManager = $attributesManager;
    }

    public function toOptionArray()
    {
        $attributes = $this->attributesManager->fetchAll();

        foreach ($attributes as $attribute) {

            $options[] = [
            'value' => $attribute->getName(),
            'label' => $attribute->getStoreLabel() . " (" . $attribute->getName() . ")",
            ];
        }

        return $options;
    }
}
