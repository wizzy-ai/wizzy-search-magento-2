<?php

namespace Wizzy\Search\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class ProductPrices extends AbstractModel implements IdentityInterface
{

    const CACHE_TAG = 'wizzy_product_prices';

    protected $_cacheTag = 'wizzy_product_prices';

    protected $_eventPrefix = 'wizzy_product_prices';

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function _construct()
    {
        $this->_init(\Wizzy\Search\Model\ResourceModel\ProductPrices::class);
    }

    public function getDefaultValues()
    {
        $values = [];
        return $values;
    }
}
