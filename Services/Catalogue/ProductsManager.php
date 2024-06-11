<?php

namespace Wizzy\Search\Services\Catalogue;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Wizzy\Search\Model\Indexer\Products;

class ProductsManager
{

    private $productRepository;
    private $searchCriteria;
    private $filterGroup;
    private $filterBuilder;
    private $status;
    private $visibility;
    private $configurable;
    private $productCollectionFactory;

    const MAX_PRODUCTS_TO_FETCH = 10000;

    public function __construct(
        ProductRepository $productRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroup $filterGroup,
        FilterBuilder $filterBuilder,
        Status $status,
        Configurable $configurable,
        Visibility $visibility,
        CollectionFactory $productCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->configurable = $configurable;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    public function fetchAll($page, $storeId)
    {
        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect('id');
        $products->addAttributeToFilter('status', ['in' => $this->status->getVisibleStatusIds()]);
        $products->addStoreFilter($storeId);
        $products->setPage($page, self::MAX_PRODUCTS_TO_FETCH);
        return $products;
    }

    public function fetchAllByCategoryIds($page, $storeId, $categoryIDs)
    {
        $products = $this->productCollectionFactory->create();
        $products->addAttributeToSelect('id');
        $products->addAttributeToFilter('status', ['in' => $this->status->getVisibleStatusIds()]);
        $products->addCategoriesFilter(['in' => $categoryIDs]);
        $products->addStoreFilter($storeId);
        $products->setPage($page, self::MAX_PRODUCTS_TO_FETCH);
        return $products;
    }

    public function getAllProductIds($storeId)
    {
        $page = 1;
        $productIds = [];

        while (true) {
            $products = $this->fetchAll($page, $storeId);
            $products = $this->getProductIds($products);
            $productIds = array_merge($productIds, $products);
            $page++;
            if (count($products) < self::MAX_PRODUCTS_TO_FETCH) {
                break;
            }
        }

        return $productIds;
    }

    public function getProductIds($products)
    {
        $productIds = [];

        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }

    public function getProductsByCategoryIds($categoryIDs, $storeId)
    {
        $page = 1;
        $productIds = [];

        while (true) {
            $products = $this->fetchAllByCategoryIds($page, $storeId, $categoryIDs);
            $products = $this->getProductIds($products);
            $productIds = array_merge($productIds, $products);
            $page++;
            if (count($products) < self::MAX_PRODUCTS_TO_FETCH) {
                break;
            }
        }

        return $productIds;
    }

    /**
     * Get parent product IDs of given children IDs.
     *
     * @param $IDs
     * @return array
     */
    public function getParentProductIds($IDs)
    {
        return array_values(array_unique($this->configurable->getParentIdsByChild($IDs)));
    }

    public function getProductsByIds($IDs, $storeId)
    {
        $filters = [
         $this->filterBuilder
            ->setField('entity_id')
            ->setConditionType('in')
            ->setValue($IDs)
            ->create(),
         $this->filterBuilder
            ->setField('store_id')
            ->setConditionType('eq')
            ->setValue($storeId)
            ->create()
        ];

        $this->filterGroup->setFilters($filters);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $products = $this->productRepository->getList($this->searchCriteria);
        $products = $products->getItems();

        return $products;
    }

    public function getProductsBySKUs($SKUs)
    {
        $filters = [
         $this->filterBuilder
            ->setField('sku')
            ->setConditionType('in')
            ->setValue($SKUs)
            ->create(),
        ];

        $this->filterGroup->setFilters($filters);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $products = $this->productRepository->getList($this->searchCriteria);
        $products = $products->getItems();
        return $products;
    }

    public function getById($productId)
    {
        return $this->productRepository->getById($productId);
    }

    public function getProductsByAttribute($attributeCode, $storeId)
    {
        $products = $this->productCollectionFactory->create();

        $products->addAttributeToSelect('id');
        $products->addStoreFilter($storeId);
        $products->addAttributeToFilter($attributeCode, [
            'notnull' => true,
        ]);

        return $products;
    }
}
