<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\PagesList;

/**
 * Class PagesSelection
 */
class PagesSelection extends AbstractFieldArray
{
   /**
    * @var PagesList
    */
   private $pagesRenderer;

   /**
    * Prepare rendering the new field by adding all the needed columns
    */
   protected function _prepareToRender()
   {
      $this->addColumn('page', [
         'label' => __('Page'),
         'renderer' => $this->getPagesRenderer(),
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
    * @return PagesList
    * @throws LocalizedException
    */
   private function getPagesRenderer() {
      if (!$this->pagesRenderer) {
         $this->pagesRenderer = $this->getLayout()->createBlock(
            PagesList::class,
            '',
            ['data' => ['is_render_to_js_template' => true]]
         );
      }
      return $this->pagesRenderer;
   }
}