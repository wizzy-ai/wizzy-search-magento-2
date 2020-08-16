<?php
declare(strict_types=1);

namespace Wizzy\Search\Model\Admin\Source\SelectBlocks;

use Magento\Framework\View\Element\Html\Select;

class Genders extends Select
{

  /**
   * Set "name" for <select> element
   *
   * @param string $value
   * @return $this
   */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

  /**
   * Set "id" for <select> element
   *
   * @param $value
   * @return $this
   */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

  /**
   * Render block HTML
   *
   * @return string
   */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return [
        ['label' => 'Men', 'value' => 'Men'],
        ['label' => 'Women', 'value' => 'Women'],
        ['label' => 'Boys', 'value' => 'Boys'],
        ['label' => 'Girls', 'value' => 'Girls'],
        ['label' => 'Kids', 'value' => 'Kids'],
        ['label' => 'Unisex', 'value' => 'Unisex'],
        ];
    }
}
