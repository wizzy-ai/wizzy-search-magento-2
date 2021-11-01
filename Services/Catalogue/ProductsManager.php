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

    public function fetchAll()
    {
        $this->filterGroup->setFilters([
         $this->filterBuilder
            ->setField('status')
            ->setConditionType('in')
            ->setValue($this->status->getVisibleStatusIds())
            ->create(),
         $this->filterBuilder
            ->setField('visibility')
            ->setConditionType('in')
            ->setValue([
               Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_IN_SEARCH
            ])
            ->create(),
        ]);

        $this->searchCriteria->setFilterGroups([$this->filterGroup]);
        $products = $this->productRepository->getList($this->searchCriteria);
        $products = $products->getItems();

        return $products;
    }

    public function getAllProductIds()
    {
        $products = $this->fetchAll();
        $products = $this->getProductIds($products);

        return $products;
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
        $filters = [
         $this->filterBuilder
            ->setField('category_id')
            ->setConditionType('in')
            ->setValue($categoryIDs)
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
