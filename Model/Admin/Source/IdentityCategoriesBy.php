<?php

namespace Wizzy\Search\Model\Admin\Source;

class IdentityCategoriesBy
{
    const SELECT = '';
    const LEVEL = 'categories-level';
    const CATEGORIES_LIST = 'categories-list';

    public function toOptionArray()
    {
        return [
        [
        'value' => self::SELECT,
        'label' => __('Select')
        ],
        [
        'value' => self::LEVEL,
        'label' => __('Categories on specific level')
        ],
        [
        'value' => self::CATEGORIES_LIST,
        'label' => __('List of Categories')
        ],
        ];
    }
}
