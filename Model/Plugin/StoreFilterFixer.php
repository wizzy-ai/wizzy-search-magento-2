<?php
namespace Wizzy\Search\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

/**
 * This plugin fixes issue with filtration by store/website ID in \Magento\Catalog\Model\ProductRepository::getList()
 */
class StoreFilterFixer
{
    /**
     * Enable filtration by store_id or website_id. The only supported condition is 'eq'
     *
     * @param ProductCollection $subject
     * @param \Closure $proceed
     * @param array $fields
     * @param string|null $condition
     * @return ProductCollection
     */
    public function aroundAddFieldToFilter(ProductCollection $subject, \Closure $proceed, $fields, $condition = null)
    {
        if (is_array($fields)) {
            foreach ($fields as $key => $filter) {
                if ($filter['attribute'] == 'website_id' && isset($filter['eq'])) {
                    $subject->addWebsiteFilter([$filter['eq']]);
                    unset($fields[$key]);
                } else if ($filter['attribute'] == 'store_id' && isset($filter['eq'])) {
                    $subject->addStoreFilter($filter['eq']);
                    unset($fields[$key]);
                }
            }
        }
        /** Do not try to pass empty $fields to addFieldToFilter, it will cause exception */
        return $fields? $proceed($fields, $condition) : $subject;
    }
}