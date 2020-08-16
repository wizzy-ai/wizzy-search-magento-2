<?php

namespace Wizzy\Search\Model\Admin\Source;

class IdentityValueBy
{
    const SELECT = '';
    const ATTRIBUTES = 'attributes';
    const CATEGORIES = 'categories';

    public function toOptionArray()
    {
        return [
        [
        'value' => self::SELECT,
        'label' => __('Select')
        ],
        [
        'value' => self::ATTRIBUTES,
        'label' => __('Attributes')
        ],
        [
        'value' => self::CATEGORIES,
        'label' => __('Categories')
        ],
        ];
    }
}
