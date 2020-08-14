<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\SwatchOptions;

/**
 * Class SwatchOptionsSelection
 */
class SwatchOptionsSelection extends AbstractFieldArray
{
   /**
    * @var SwatchOptions
    */
   private $swatchOptionsRenderer;

   /**
    * Prepare rendering the new field by adding all the needed columns
    */
   protected function _prepareToRender()
   {
      $this->addColumn('key', [
         'label' => __('Field'),
         'renderer' => $this->getSwatchOptionRenderer(),
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
   protected function _prepareArrayRow(DataObject $row): void
   {
      $options = [];
      $row->setData('option_extra_attrs', $options);
   }

   /**
    * @return SwatchOptions
    * @throws LocalizedException
    */
   private function getSwatchOptionRenderer() {
      if (!$this->swatchOptionsRenderer) {
         $this->swatchOptionsRenderer = $this->getLayout()->createBlock(
            SwatchOptions::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
         );
      }
      return $this->swatchOptionsRenderer;
   }
}