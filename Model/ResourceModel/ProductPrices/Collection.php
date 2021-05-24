<?php

namespace Wizzy\Search\Model\ResourceModel\ProductPrices;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'wizzy_product_prices';
    protected $_eventObject = 'wizzy_product_prices_collection';

   /**
    * Define resource model
    *
    * @return void
    */
    protected function _construct()
    {
        $this->_init(
            \Wizzy\Search\Model\ProductPrices::class,
            \Wizzy\Search\Model\ResourceModel\ProductPrices::class
        );
    }
}
