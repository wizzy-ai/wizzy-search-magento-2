<?php

namespace Wizzy\Search\Model\Admin\Source;

class CategoriesRenderSelection
{
   const SIMPLE_LIST = 'list';
   const HIERARCHY = 'hierarchy';

   public function toOptionArray() {
      return [
         [
            'value' => self::SIMPLE_LIST,
            'label' => __('Linear List')
         ],
         [
            'value' => self::HIERARCHY,
            'label' => __('Hierarchical')
         ],
      ];
   }
}