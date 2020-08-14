<?php
declare(strict_types=1);

namespace Wizzy\Search\Model\Admin\Source\SelectBlocks;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Wizzy\Search\Services\Catalogue\AttributesManager;

class Attributes extends Select {

  private $attributesManager;

  public function __construct(Context $context, AttributesManager $attributesManager, array $data = []) {
    parent::__construct($context, $data);
    $this->attributesManager = $attributesManager;
  }

  /**
   * Set "name" for <select> element
   *
   * @param string $value
   * @return $this
   */
  public function setInputName($value) {
    return $this->setName($value);
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
    return parent::_toHtml();
  }

  private function getSourceOptions(): array {
    $attributes = $this->attributesManager->fetchAll();
    $options = [];

    foreach ($attributes as $attribute) {
      $options[] = [
        'value' => $attribute->getId(),
        'label' => $attribute->getStoreLabel() . " (" . $attribute->getName() . ")",
      ];
    }

    return $options;
  }
}