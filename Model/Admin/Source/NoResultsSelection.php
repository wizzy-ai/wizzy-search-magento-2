<?php

namespace Wizzy\Search\Model\Admin\Source;

class NoResultsSelection
{
   const HIDE_MENU = 'hide_menu';
   const DISPLAY_NO_RESULTS_MESSAGE = 'show_no_results_message';

   public function toOptionArray() {
      return [
         [
            'value' => self::HIDE_MENU,
            'label' => __('Hide Menu')
         ],
         [
            'value' => self::DISPLAY_NO_RESULTS_MESSAGE,
            'label' => __('Display no results message')
         ],
      ];
   }
}