<?php

namespace Wizzy\Search\Services\Request;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\App\RequestInterface;

class CategoryManager
{

    private $resolver;
    private $request;

    public function __construct(Resolver $resolver, RequestInterface $request)
    {
        $this->resolver = $resolver;
        $this->request = $request;
    }

    public function getCategory()
    {
        return $this->resolver->get()->getCurrentCategory();
    }

    public function isCategoryReplaceable()
    {
        $category = $this->getCategory();
        return (
            $this->isOnCategoryPage() &&
            $category->getDisplayMode() !== "PAGE"
        );
    }

    public function isOnCategoryPage()
    {
        return (
         $this->request->getControllerName() === "category" &&
         $this->request->getFullActionName() === 'catalog_category_view'
        );
    }

    public function getCategoryEndpoint()
    {
        $categoryUrl = $this->getCategory()->getUrl();
        $categoryPath = $this->getCategory()->getData('url_path');
        if (strpos($categoryUrl, '.html') === true) {
            return '/' . $categoryPath . '.html';
        } else {
            return '/' . $categoryPath;
        }
    }
}
