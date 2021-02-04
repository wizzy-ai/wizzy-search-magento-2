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

    public function fetchAllOfCurrentStore($includeLevelOne = false)
    {
        return $this->fetchAll($this->storeManager->getCurrentStoreId(), $includeLevelOne);
    }

    public function fetchAll($storeId, $includeLevelOne = false)
    {
        $categories = $this->categoryColleection->create()
         ->distinct(true)
         ->addAttributeToSelect('*')
         ->setStore($storeId);

        if ($includeLevelOne === false) {
            $categories = $categories->addAttributeToFilter('level', ['gt' => 1]);
        }

        return $categories;
    }

   /**
    *  Get root category of the store.
    */
    public function getRootCategory($storeId)
    {
        $store = $this->storeManager->getStoreById($storeId);
        $categoryId = $store->getRootCategoryId();
        $categories = $this->fetchByIds([$categoryId], $storeId, true);
        if (count($categories)) {
            return $categories->getFirstItem();
        }

        return null;
    }

    /**
     * Get parent IDs of Given Category
     *
     * @param $category
     * @return mixed
     */
    public function getParentIdsOfCategory($category)
    {
        if (!$category) {
            return [];
        }
        $parentIds = $category->getParentIds();
        if (($key = array_search(1, $parentIds)) !== false) {
            unset($parentIds[$key]);
        }
        return $parentIds;
    }

    public function fetchByIds($categoryIds, $storeId, $includeAnyLevel = false)
    {
        if (!$categoryIds || !count($categoryIds)) {
            return [];
        }
        $categories = $this->categoryColleection->create()
         ->addAttributeToSelect('*')
         ->setStore($storeId)
         ->addAttributeToFilter('entity_id', $categoryIds);

        if ($includeAnyLevel === false) {
            $categories = $categories->addAttributeToFilter('level', ['gt' => 1]);
        }

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

    public function fetchAllDescendantsByParentIds($categoryIds, $storeId)
    {
        $categories = $this->fetchByIds($categoryIds, $storeId, true);
        $paths = [];

        foreach ($categories as $category) {
            $paths[] = $category->getPath() . "/";
        }

        if (count($paths) === 0) {
            return [];
        }

        $descendantsCategories = $this->categoryColleection->create()
          ->addAttributeToSelect('*')
          ->addPathsFilter($paths)
          ->setStore($storeId);

        return $descendantsCategories;
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
