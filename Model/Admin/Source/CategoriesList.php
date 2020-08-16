<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\CategoriesManager;

class CategoriesList
{
    private $categoriesManager;

    public function __construct(CategoriesManager $categoriesManager)
    {
        $this->categoriesManager = $categoriesManager;
    }

    public function toOptionArray()
    {
        $categories = $this->categoriesManager->fetchAllOfCurrentStore();

        foreach ($categories as $category) {
            $options[] = [
            'value' => $category->getId(),
            'label' => $category->getName()
            ];
        }

        return $options;
    }
}
