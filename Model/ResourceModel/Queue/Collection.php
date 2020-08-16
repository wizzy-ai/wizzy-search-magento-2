<?php

namespace Wizzy\Search\Model\ResourceModel\Queue;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'wizzy_sync_queue';
    protected $_eventObject = 'sync_queue';

  /**
   * Define resource model
   *
   * @return void
   */
    protected function _construct()
    {
        $this->_init(Wizzy\Search\Model\Queue::class, Wizzy\Search\Model\ResourceModel\Queue::class);
    }
}
