<?php

namespace Wizzy\Search\Model\Admin\Source;

class PaginationType
{
   const INFINITE_SCROLL = 'infinite_scroll';
   const NUMBERED_PAGINATION = 'numbered_pagination';

   public function toOptionArray() {
      return [
         [
            'value' => self::INFINITE_SCROLL,
            'label' => __('Infinite Scroll')
         ],
         [
            'value' => self::NUMBERED_PAGINATION,
            'label' => __('Numbered Pagination')
         ],
      ];
   }
}