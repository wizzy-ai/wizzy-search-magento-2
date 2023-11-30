<?php

namespace Wizzy\Search\Model\ResourceModel\DeletedProducts;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'wizzy_deleted_products';
    protected $_eventObject = 'deleted_products_collection';

  /**
   * Define resource model
   *
   * @return void
   */
    protected function _construct()
    {
        $this->_init(
            \Wizzy\Search\Model\DeletedProducts::class,
            \Wizzy\Search\Model\ResourceModel\DeletedProducts::class
        );
    }
}
