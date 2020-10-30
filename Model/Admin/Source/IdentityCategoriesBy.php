<?php

namespace Wizzy\Search\Model\Admin\Source;

class IdentityCategoriesBy
{
    const SELECT = '';
    const LEVEL = 'categories-level';
    const CATEGORIES_LIST = 'categories-list';
    const ALL_SUB_CATEGORIES = 'all-sub-categories-list';

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
        [
        'value' => self::ALL_SUB_CATEGORIES,
        'label' => __('All Sub Categories of Selected Categories')
        ],
        ];
    }
}
