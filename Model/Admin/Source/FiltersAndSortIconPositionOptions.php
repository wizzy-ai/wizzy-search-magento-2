<?php

namespace Wizzy\Search\Model\Admin\Source;

class FiltersAndSortIconPositionOptions
{
    const BOTTTOM = 'bottom';
    const TOP = 'top';

    public function toOptionArray()
    {
        return [
         [
            'value' => self::BOTTTOM,
            'label' => __('Bottom')
         ],
         [
            'value' => self::TOP,
            'label' => __('Top')
         ],
        ];
    }
}
