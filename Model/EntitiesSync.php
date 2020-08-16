<?php

namespace Wizzy\Search\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class EntitiesSync extends AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'wizzy_entities_sync';

    protected $_cacheTag = 'wizzy_entities_sync';

    protected $_eventPrefix = 'wizzy_entities_sync';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(Wizzy\Search\Model\ResourceModel\EntitiesSync::class);
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
