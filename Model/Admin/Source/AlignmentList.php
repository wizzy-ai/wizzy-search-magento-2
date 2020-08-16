<?php

namespace Wizzy\Search\Model\Admin\Source;

class AlignmentList
{
    const LEFT_ALIGNED = 'left';
    const RIGHT_ALIGNED = 'right';

    public function toOptionArray()
    {
        return [
         [
            'value' => self::LEFT_ALIGNED,
            'label' => __('Left')
         ],
         [
            'value' => self::RIGHT_ALIGNED,
            'label' => __('Right')
         ],
        ];
    }
}
