<?php
declare(strict_types=1);

namespace Wizzy\Search\Model\Admin\Source\SelectBlocks;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Wizzy\Search\Services\Catalogue\AttributesManager;

class Facets extends Select
{

    private $attributesManager;

    public function __construct(Context $context, AttributesManager $attributesManager, array $data = [])
    {
        parent::__construct($context, $data);
        $this->attributesManager = $attributesManager;
    }

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
        $attributes = $this->attributesManager->fetchAll();

        $options = $this->getDefaultOptions();

        foreach ($attributes as $attribute) {
            $options[] = [
            'value' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel() . " (" . $attribute->getName() . ")",
            ];
        }

        return $options;
    }

    private function getDefaultOptions()
    {
        return [
         [
            'value' => 'all',
            'label' => 'All Fields',
         ],
         [
            'value' => 'categories',
            'label' => 'Categories',
         ],
         [
            'value' => 'brands',
            'label' => 'Brands',
         ],
         [
            'value' => 'sellingPrice',
            'label' => 'Price (Selling Price)',
         ],
         [
            'value' => 'genders',
            'label' => 'Gender',
         ],
         [
            'value' => 'colors',
            'label' => 'Colors',
         ],
         [
            'value' => 'sizes',
            'label' => 'Sizes',
         ],
         [
            'value' => 'avgRatings',
            'label' => 'Avg Ratings',
         ],
         [
            'value' => 'discountPercentage',
            'label' => 'Discount',
         ],
         [
            'value' => 'inStock',
            'label' => 'Availability',
         ],
         [
            'value' => 'attributes',
            'label' => 'All Attributes (Filterable)',
         ],
        ];
    }
}
