<?php
namespace Wizzy\Search\Model\Admin\Source;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

use Wizzy\Search\Model\Admin\Source\SelectBlocks\Categories;
use Wizzy\Search\Model\Admin\Source\SelectBlocks\Genders;

/**
 * Class GenderIdentificationCategories
 */
class GenderIdentificationCategories extends AbstractFieldArray
{

  /**
   * @var Genders
   */
  private $gendersRenderer;

  /**
   * @var Categories
   */
  private $categoriesRenderer;

  /**
   * Prepare rendering the new field by adding all the needed columns
   */
  protected function _prepareToRender()
  {
    $this->addColumn('gender', [
      'label' => __('Gender/Age Group'),
      'renderer' => $this->getGendersRenderer(),
    ]);
    $this->addColumn('categories', [
      'label' => __('Categories'),
      'renderer' => $this->getCategoriesRenderer(),
      'extra_params' => 'multiple="multiple"'
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
    $categories = $row->getCategories();
    if ($categories && count($categories) > 0) {
      foreach ($categories as $category) {
        $options['option_' . $this->getCategoriesRenderer()->calcOptionHash($category)] = 'selected="selected"';
      }
    }
    $row->setData('option_extra_attrs', $options);
  }

  /**
   * @return Genders
   * @throws LocalizedException
   */
  private function getGendersRenderer() {
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
   * @return Categories
   * @throws LocalizedException
   */
  private function getCategoriesRenderer() {
    if (!$this->categoriesRenderer) {
      $this->categoriesRenderer = $this->getLayout()->createBlock(
        Categories::class,
        '',
        ['data' => ['is_render_to_js_template' => true]]
      );
    }
    return $this->categoriesRenderer;
  }
}