<?php

namespace Wizzy\Search\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Wizzy\Search\Helpers\DB\WizzyTables;

class Queue extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(WizzyTables::$SYNC_QUEUE_TABLE_NAME, 'id');
    }
}
