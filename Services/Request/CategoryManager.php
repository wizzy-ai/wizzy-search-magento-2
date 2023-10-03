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
        try {
            return $this->resolver->get()->getCurrentCategory();
        } catch (\Exception $exception) {
            return null;
        }
    }

    public function isCategoryReplaceable()
    {
        $category = $this->getCategory();
        return (
            $category && $this->isOnCategoryPage() &&
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
        $category = $this->getCategory();
        if (!$category) {
            return '/';
        }
        $categoryUrl = $this->getCategory()->getUrl();
        $categoryPath = $this->getCategory()->getData('url_path');
        if (strpos($categoryUrl, '.html') !== false) {
            return '/' . $categoryPath . '.html';
        } else {
            return '/' . $categoryPath;
        }
    }
}
