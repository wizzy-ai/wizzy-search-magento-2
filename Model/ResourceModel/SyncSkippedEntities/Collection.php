<?php

namespace Wizzy\Search\Model\ResourceModel\SyncSkippedEntities;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'wizzy_sync_skipped_entities';
    protected $_eventObject = 'sync_skipped_entities_collection';

   /**
    * Define resource model
    *
    * @return void
    */
    protected function _construct()
    {
        $this->_init(
            \Wizzy\Search\Model\SyncSkippedEntities::class,
            \Wizzy\Search\Model\ResourceModel\SyncSkippedEntities::class
        );
    }
}
