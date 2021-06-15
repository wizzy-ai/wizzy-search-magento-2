<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\Attributes;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\AutocompletePositions;

class AutocompleteAttributeSelection extends AbstractFieldArray
{

  /**
   * @var AutocompletePositions
   */
    private $positionsRenderer;

  /**
   * @var Attributes
   */
    private $attributesRenderer;

  /**
   * Prepare rendering the new field by adding all the needed columns
   */
    protected function _prepareToRender()
    {
        $this->addColumn('attribute', [
        'label' => __('Attribute'),
        'renderer' => $this->getAttributesRenderer(),
        ]);
        $this->addColumn('position', [
        'label' => __('Position'),
        'renderer' => $this->getPositionsRenderer(),
        ]);
        $this->addColumn('autocomplete_glue', ['label' => __('Glue')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

  /**
   * Prepare existing row data object
   *
   * @param DataObject $row
   * @throws LocalizedException
   */
    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $row->setData('option_extra_attrs', $options);
    }

  /**
   * @return AutocompletePositions
   * @throws LocalizedException
   */
    private function getPositionsRenderer()
    {
        if (!$this->positionsRenderer) {
            $this->positionsRenderer = $this->getLayout()->createBlock(
                AutocompletePositions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->positionsRenderer;
    }

  /**
   * @return Attributes
   * @throws LocalizedException
   */
    private function getAttributesRenderer()
    {
        if (!$this->attributesRenderer) {
            $this->attributesRenderer = $this->getLayout()->createBlock(
                Attributes::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->attributesRenderer;
    }
}
