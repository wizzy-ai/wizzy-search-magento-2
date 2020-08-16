<?php

namespace Wizzy\Search\Model\Admin\Source;

class InstantSearchBehaviours
{
    const SEARCH_AS_YOU_TYPE = 'search_as_you_type';
    const ON_FORM_SUBMISSION = 'on_form_submmission';

    public function toOptionArray()
    {
        return [
         [
            'value' => self::SEARCH_AS_YOU_TYPE,
            'label' => __('Search as You Type')
         ],
         [
            'value' => self::ON_FORM_SUBMISSION,
            'label' => __('On Form Submission')
         ],
        ];
    }
}
