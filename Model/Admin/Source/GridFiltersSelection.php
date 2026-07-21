<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\Facets;

class GridFiltersSelection extends AbstractFieldArray
{
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
        $this->addColumn('question', [
            'label' => __('Question'),
            'class' => 'required-entry validate-no-empty',
        ]);
        $this->addColumn('after_web', [
            'label' => __('After (Web)'),
            'class' => 'required-entry validate-number',
        ]);
        $this->addColumn('after_mobile', [
            'label' => __('After (Mobile)'),
            'class' => 'required-entry validate-number',
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
        $key = $row->getData('key');

        if (!empty($key)) {
            $hash = $this->getFacetsOptionRenderer()->calcOptionHash($key);
            $options['option_' . $hash] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
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
