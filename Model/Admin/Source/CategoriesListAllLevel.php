<?php

namespace Wizzy\Search\Model\Admin\Source;

use Wizzy\Search\Services\Catalogue\CategoriesManager;

class CategoriesListAllLevel
{
    private $categoriesManager;

    public function __construct(CategoriesManager $categoriesManager)
    {
        $this->categoriesManager = $categoriesManager;
    }

    public function toOptionArray()
    {
        $categories = $this->categoriesManager->fetchAllOfCurrentStore(true);

        foreach ($categories as $category) {
            $categoryLabel = $category->getName();
            if ($category->getUrlKey()) {
                $categoryLabel .= " (" . $category->getUrlKey() . ")";
            }

            $options[] = [
            'value' => $category->getId(),
            'label' => $categoryLabel,
            ];
        }

        usort($options, function ($categoryA, $categoryB) {
            return strcmp($categoryA["label"], $categoryB["label"]);
        });

        return $options;
    }
}
