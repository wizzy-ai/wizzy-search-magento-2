<?php

namespace Wizzy\Search\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class DeletedProducts extends AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'wizzy_deleted_products';

    protected $_cacheTag = 'wizzy_deleted_products';

    protected $_eventPrefix = 'wizzy_deleted_products';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(\Wizzy\Search\Model\ResourceModel\DeletedProducts::class);
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
