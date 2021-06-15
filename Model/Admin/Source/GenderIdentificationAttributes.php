<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\Attributes;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\Genders;

class GenderIdentificationAttributes extends AbstractFieldArray
{

  /**
   * @var Genders
   */
    private $gendersRenderer;

  /**
   * @var Attributes
   */
    private $attributesRenderer;

  /**
   * Prepare rendering the new field by adding all the needed columns
   */
    protected function _prepareToRender()
    {
        $this->addColumn('gender', [
        'label' => __('Gender/Age Group'),
        'renderer' => $this->getGendersRenderer(),
        ]);
        $this->addColumn('attribute', [
        'label' => __('Attribute'),
        'renderer' => $this->getAttributesRenderer(),
        ]);
        $this->addColumn('attribute_value', ['label' => __('Value'), 'class' => 'required-entry validate-no-empty']);
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
   * @return Genders
   * @throws LocalizedException
   */
    private function getGendersRenderer()
    {
        if (!$this->gendersRenderer) {
            $this->gendersRenderer = $this->getLayout()->createBlock(
                Genders::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->gendersRenderer;
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
