<?php

namespace Wizzy\Search\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Wizzy\Search\Helpers\DB\WizzyTables;

class SyncSkippedEntities extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(WizzyTables::$SYNC_SKIPPED_ENTITIES_TABLE_NAME, 'id');
    }
}
