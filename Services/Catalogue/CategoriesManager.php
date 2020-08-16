<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Wizzy\Search\Services\Store\StoreManager;

class CategoriesManager
{

    private $categoryColleection;
    private $storeManager;

    public function __construct(CollectionFactory $categoryColleection, StoreManager $storeManager)
    {
        $this->categoryColleection = $categoryColleection;
        $this->storeManager = $storeManager;
    }

    public function fetchAllOfCurrentStore()
    {
        return $this->fetchAll($this->storeManager->getCurrentStoreId());
    }

    public function fetchAll($storeId)
    {
        $categories = $this->categoryColleection->create()
         ->distinct(true)
         ->addAttributeToFilter('level', ['gt' => 1])
         ->addAttributeToSelect('*')
         ->setStore($storeId);

        return $categories;
    }

    public function fetchByIds($categoryIds, $storeId)
    {
        $categories = $this->categoryColleection->create()
         ->addAttributeToSelect('*')
         ->addAttributeToFilter('level', ['gt' => 1])
         ->setStore($storeId)
         ->addAttributeToFilter('entity_id', $categoryIds);

        return $categories;
    }

    public function fetch($categoryIds, $storeId)
    {
        if (!is_array($categoryIds)) {
            $categoryIds = [$categoryIds];
        }
        $categories = $this->categoryColleection->create()
         ->addAttributeToSelect('*')
         ->setStore($storeId)
         ->addAttributeToFilter('entity_id', $categoryIds);

        return $categories;
    }

    public function fetchAllByLevel($level, $storeId = "")
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getCurrentStoreId();
        }
        $categories = $this->fetchAll($storeId);
        $levelCategories = [];

        foreach ($categories as $category) {
            if ($category->getLevel() == $level) {
                $levelCategories[] = $category;
            }
        }

        return $levelCategories;
    }

    private function getMaxCategoryLevel()
    {
        $maxLevel = 0;
        $categories = $this->fetchAll($this->storeManager->getCurrentStoreId());

        foreach ($categories as $category) {
            if ($maxLevel < $category->getLevel()) {
                $maxLevel = $category->getLevel();
            }
        }

        return $maxLevel;
    }

    public function getLevels()
    {
        $maxLevel = $this->getMaxCategoryLevel();
        $levels = [];

        while ($maxLevel > 0) {
            $levels[] = [
            'label' => 'Level ' . $maxLevel,
            'key' => 'level-' . $maxLevel,
            ];
            $maxLevel--;
        }

        return $levels;
    }
}
