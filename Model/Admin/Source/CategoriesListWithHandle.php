<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\CategoriesManager;

class CategoriesListWithHandle
{
    private $categoriesManager;

    public function __construct(CategoriesManager $categoriesManager)
    {
        $this->categoriesManager = $categoriesManager;
    }

    public function toOptionArray()
    {
        $categories = $this->categoriesManager->fetchAllOfCurrentStore();
        $options = [];
        foreach ($categories as $category) {
            $options[] = [
            'value' => $category->getUrlKey(),
            'label' => $category->getName()
            ];
        }

        return $options;
    }
}
