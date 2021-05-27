<?php

namespace Wizzy\Search\Model\Admin\Source;

class FormSubmissionBehaviours
{
    const REPLACE_COLUMNS_CONTAINER = 'replace_columns_on_same_page';
    const REDIRECT_PAGE = 'redirect_page';

    public function toOptionArray()
    {
        return [
            [
                'value' => self::REPLACE_COLUMNS_CONTAINER,
                'label' => __('Replace Columns on Same Page (Recommended)')
            ],
            [
                'value' => self::REDIRECT_PAGE,
                'label' => __('Redirect Page')
            ],
        ];
    }
}
