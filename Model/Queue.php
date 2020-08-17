<?php

namespace Wizzy\Search\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'wizzy_sync_queue';

    protected $_cacheTag = 'wizzy_sync_queue';

    protected $_eventPrefix = 'wizzy_sync_queue';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(\Wizzy\Search\Model\ResourceModel\Queue::class);
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
