<?php

namespace Wizzy\Search\Model\Admin\Source;

class LeftFacetsDefaultBehaviourSelection
{
    const COLLAPSED = 'COLLAPSED';
    const OPENED = 'OPENED';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::OPENED,
                'label' => __('Opened')
            ],
            [
                'value' => self::COLLAPSED,
                'label' => __('Collapsed')
            ],
        ];
    }
}
