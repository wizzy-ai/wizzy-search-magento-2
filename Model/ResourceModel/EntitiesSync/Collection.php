<?php

namespace Wizzy\Search\Model\ResourceModel\EntitiesSync;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'wizzy_entities_sync';
    protected $_eventObject = 'entities_sync_collection';

  /**
   * Define resource model
   *
   * @return void
   */
    protected function _construct()
    {
        $this->_init(\Wizzy\Search\Model\EntitiesSync::class, \Wizzy\Search\Model\ResourceModel\EntitiesSync::class);
    }
}
