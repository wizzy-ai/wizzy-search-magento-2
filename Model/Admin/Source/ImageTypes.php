<?php

namespace Wizzy\Search\Model\Admin\Source;

class ImageTypes
{
    const THUMBNAIL = 'thumbnail';
    const BASE = 'base';
    const SMALL = 'small';

    public function toOptionArray()
    {
        return [
         [
            'value' => self::THUMBNAIL,
            'label' => __('thumbnail')
         ],
         [
            'value' => self::BASE,
            'label' => __('base')
         ],
         [
            'value' => self::SMALL,
            'label' => __('small')
         ],
        ];
    }
}
