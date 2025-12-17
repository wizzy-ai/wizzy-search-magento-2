<?php

namespace Wizzy\Search\Model\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Psr\Log\LoggerInterface;
use Wizzy\Search\Services\Queue\Processors\IndexCategoryProductsProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Indexer\IndexerManager;
use Wizzy\Search\Services\Model\WizzyProduct;
use Magento\Framework\App\ResourceConnection;

class CategoriesObserver
{
    protected $indexer;
    protected $logger;
    protected $categoryRepository;
    protected $queueManager;
    protected $indexerManager;
    protected $storeManager;
    protected $productsManager;
    protected $wizzyProduct;
    protected $indexCategoryProductsProcessor;
    protected $resource;

    public function __construct(
        LoggerInterface $logger,
        CategoryRepositoryInterface $categoryRepository,
        QueueManager $queueManager,
        IndexerManager $indexerManager,
        StoreManager $storeManager,
        WizzyProduct $wizzyProduct,
        IndexCategoryProductsProcessor $indexCategoryProductsProcessor,
        ResourceConnection $resource
    ) {
        $this->logger = $logger;
        $this->categoryRepository = $categoryRepository;
        $this->queueManager = $queueManager;
        $this->indexer = $indexerManager->getProductsIndexer();
        $this->storeManager = $storeManager;
        $this->wizzyProduct = $wizzyProduct;
        $this->indexCategoryProductsProcessor = $indexCategoryProductsProcessor;
        $this->resource = $resource;
    }

    public function beforeSave(Category $category)
    {
        $changes = [];
        $origData = $category->getOrigData();
        $storeId = $category->getStoreId();
        $data = [
            'categoryIds' => [$category->getId()],
        ];
     
        $origName = $origData['name'] ?? null;
        $origUrlKey = $origData['url_key'] ?? null;
        $origIsActive = $origData['is_active'] ?? null;

        if ($category->getName() !== null && $category->getName() !== '' &&
            $category->getName() !== $origName) {
            $changes[] = 'name';
        }

        if ($category->getUrlKey() !== null && $category->getUrlKey() !== '' &&
            $category->getUrlKey() !== $origUrlKey) {
            $changes[] = 'url_key';
        }

        if ($category->getIsActive() !== null && (int)$category->getIsActive() !== (int)($origIsActive)) {
            $changes[] = 'is_active';
        }
      
        if (array_intersect($changes, ['name', 'url_key', 'is_active'])) {
            $this->queueManager->enqueue(IndexCategoryProductsProcessor::class, $storeId, $data);
        }

        $newPositions = $category->getPostedProducts() ?? [];
        $changedProductIds = [];
        $removedProductIds = [];

        if (!empty($newPositions) && $category->getId() && $storeId == 0) {
            try {
                $existingPositions = $this->getCategoryProductPositions($category->getId());
                $existingProductIds = array_keys($existingPositions);
                $newProductIds = array_keys($newPositions);

                foreach ($newPositions as $productId => $newPosition) {
                    $oldPosition = $existingPositions[$productId] ?? null;
                    if ($oldPosition !== null && (int)$oldPosition !== (int)$newPosition) {
                        $changedProductIds[] = $productId;
                    }
                }

                $removedProductIds = array_diff($existingProductIds, $newProductIds);

                $affectedProductIds = array_values(array_unique(array_merge($changedProductIds, $removedProductIds)));
                if (!empty($affectedProductIds)) {
                    $this->wizzyProduct->addProductsInSync($affectedProductIds, $category->getStoreId());
                }

            } catch (\Exception $e) {
                $this->logger->error("Error processing category product positions: " . $e->getMessage());
            }
        }

        return [$category];
    }
    protected function getCategoryProductPositions($categoryId): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('catalog_category_product');

        $positions = [];
        $page = 1;
        $pageSize = 10000;

        do {
            $select = $connection->select()
                ->from($table, ['product_id', 'position'])
                ->where('category_id = ?', $categoryId)
                ->limitPage($page, $pageSize);

            $result = $connection->fetchPairs($select);

            if (!empty($result)) {
                $positions += $result;
            }

            $page++;

        } while (!empty($result));

        return $positions;
    }
}
