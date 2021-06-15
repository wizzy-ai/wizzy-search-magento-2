<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\SortOptions;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\SortOrder;

class SortOptionsSelection extends AbstractFieldArray
{
   /**
    * @var SortOrder
    */
    private $sortOrderRenderer;

   /**
    * @var SortOptions
    */
    private $sortOptionRenderer;

   /**
    * Prepare rendering the new field by adding all the needed columns
    */
    protected function _prepareToRender()
    {
        $this->addColumn('field', [
         'label' => __('Field'),
         'renderer' => $this->getSortOptionRenderer(),
        ]);
        $this->addColumn('label', ['label' => __('Label'), 'class' => 'required-entry validate-no-empty']);
        $this->addColumn('order', [
         'label' => __('Order'),
         'renderer' => $this->getSortOrderRenderer(),
        ]);

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
    * @return SortOrder
    * @throws LocalizedException
    */
    private function getSortOrderRenderer()
    {
        if (!$this->sortOrderRenderer) {
            $this->sortOrderRenderer = $this->getLayout()->createBlock(
                SortOrder::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sortOrderRenderer;
    }

   /**
    * @return SortOptions
    * @throws LocalizedException
    */
    private function getSortOptionRenderer()
    {
        if (!$this->sortOptionRenderer) {
            $this->sortOptionRenderer = $this->getLayout()->createBlock(
                SortOptions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->sortOptionRenderer;
    }
}
