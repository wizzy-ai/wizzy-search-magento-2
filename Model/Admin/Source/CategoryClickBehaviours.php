<?php

namespace Wizzy\Search\Model\Admin\Source;

class CategoryClickBehaviours
{
    const HIT_SEARCH_WITH_CATEGORY_KEYWORD = 'hit_search_with_category_keyword';
    const OPEN_CATEGORY_PAGE = 'open_category_page';

    public function toOptionArray()
    {
        return [
         [
            'value' => self::HIT_SEARCH_WITH_CATEGORY_KEYWORD,
            'label' => __('Hit Search with Category Keyword (Preferred)')
         ],
         [
            'value' => self::OPEN_CATEGORY_PAGE,
            'label' => __('Open Category Page')
         ],
        ];
    }
}
