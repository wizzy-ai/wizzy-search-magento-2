<?php
declare(strict_types=1);

namespace Wizzy\Search\Model\Admin\Source\SelectBlocks;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Wizzy\Search\Services\Catalogue\CategoriesManager;

class Categories extends Select {

  private $categoriesManager;

  public function __construct(Context $context, CategoriesManager $categoriesManager, array $data = []) {
    parent::__construct($context, $data);
    $this->categoriesManager = $categoriesManager;
  }

  /**
   * Set "name" for <select> element
   *
   * @param string $value
   * @return $this
   */
  public function setInputName($value) {
    return $this->setName($value . '[]');
  }

  /**
   * Set "id" for <select> element
   *
   * @param $value
   * @return $this
   */
  public function setInputId($value) {
    return $this->setId($value);
  }

  /**
   * Render block HTML
   *
   * @return string
   */
  public function _toHtml(): string {
    if (!$this->getOptions()) {
      $this->setOptions($this->getSourceOptions());
    }
    $this->setExtraParams('multiple="multiple"');
    $this->setClass('admin__control-multiselect admin-dynamic-config-multiselect');
    return parent::_toHtml();
  }

  private function getSourceOptions(): array {
    $categories = $this->categoriesManager->fetchAllOfCurrentStore();
    $options = [];

    foreach ($categories as $category) {
      $options[] = [
        'value' => $category->getId(),
        'label' => $category->getName()
      ];
    }

    return $options;
  }
}