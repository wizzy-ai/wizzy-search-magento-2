<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\CategoriesManager;

class CategoryLevels
{
  private $categoriesManager;
  public function __construct(CategoriesManager $categoriesManager) {
    $this->categoriesManager = $categoriesManager;
  }

  const SELECT = '';

  public function toOptionArray() {
    $levels = $this->categoriesManager->getLevels();

    $options = [
      [
        'value' => self::SELECT,
        'label' => __('Select'),
      ]
    ];

    foreach ($levels as $level) {
      $options[] = [
        'value' => $level['key'],
        'label' => __($level['label'])
      ];
    }

    return $options;
  }
}