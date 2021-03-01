<?php

namespace Wizzy\Search\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class SyncSkippedEntities extends AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'wizzy_sync_skipped_entities';

    protected $_cacheTag = 'wizzy_sync_skipped_entities';

    protected $_eventPrefix = 'wizzy_sync_skipped_entities';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(\Wizzy\Search\Model\ResourceModel\SyncSkippedEntities::class);
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
