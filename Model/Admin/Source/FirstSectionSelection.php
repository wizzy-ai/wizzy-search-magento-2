<?php

namespace Wizzy\Search\Model\Admin\Source;

class FirstSectionSelection
{
    const CATEGORIES_SECTION = 'categories';
    const OTHERS_SECTION = 'others';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::CATEGORIES_SECTION,
                'label' => __('Categories')
            ],
            [
                'value' => self::OTHERS_SECTION,
                'label' => __('Others')
            ],
        ];
    }
}
