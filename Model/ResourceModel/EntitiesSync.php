<?php

namespace Wizzy\Search\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Wizzy\Search\Helpers\DB\WizzyTables;

class EntitiesSync extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(WizzyTables::$ENTITIES_SYNC_TABLE_NAME, 'id');
    }
}
