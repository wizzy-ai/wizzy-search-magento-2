<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\FacetPositions;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\Facets;

class FacetsSelection extends AbstractFieldArray
{
   /**
    * @var FacetPositions
    */
    private $positionsRenderer;

   /**
    * @var Facets
    */
    private $facetsOptionRenderer;

   /**
    * Prepare rendering the new field by adding all the needed columns
    */
    protected function _prepareToRender()
    {
        $this->addColumn('key', [
         'label' => __('Field'),
         'renderer' => $this->getFacetsOptionRenderer(),
        ]);
        $this->addColumn('label', ['label' => __('Label'), 'class' => 'required-entry validate-no-empty']);
        $this->addColumn('position', [
         'label' => __('Position'),
         'renderer' => $this->getPositionsRenderer(),
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
    * @return FacetPositions
    * @throws LocalizedException
    */
    private function getPositionsRenderer()
    {
        if (!$this->positionsRenderer) {
            $this->positionsRenderer = $this->getLayout()->createBlock(
                FacetPositions::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->positionsRenderer;
    }

   /**
    * @return Facets
    * @throws LocalizedException
    */
    private function getFacetsOptionRenderer()
    {
        if (!$this->facetsOptionRenderer) {
            $this->facetsOptionRenderer = $this->getLayout()->createBlock(
                Facets::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->facetsOptionRenderer;
    }
}
