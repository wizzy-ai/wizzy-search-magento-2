<?php

namespace Wizzy\Search\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Wizzy\Search\Helpers\DB\WizzyTables;

class ProductPrices extends AbstractDb
{
    protected function _construct()
    {
        $this->_init(WizzyTables::$PRODUCT_PRICES, 'id');
    }
}
