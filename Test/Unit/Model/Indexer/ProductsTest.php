<?php

namespace Wizzy\Search\Test\Unit\Model\Indexer;

use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Indexer\Products;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Indexer\ProductPricesHelper;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\IndexProductsProcessor;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreManager;

class ProductsTest extends TestCase
{
    public function testExecuteListWithExplicitStoreOnlyQueuesThatStore(): void
    {
        $storeId = 7;
        $productIds = [11, 12];
        $productsManager = $this->createMock(ProductsManager::class);
        $queueManager = $this->createMock(QueueManager::class);
        $entitiesSync = $this->createMock(EntitiesSync::class);
        $storeManager = $this->createMock(StoreManager::class);
        $productPricesHelper = $this->createMock(ProductPricesHelper::class);
        $output = $this->createMock(IndexerOutput::class);
        $storeAdvancedConfig = $this->createMock(StoreAdvancedConfig::class);

        $storeManager->expects($this->once())
            ->method('getToSyncStoreIds')
            ->with($storeId)
            ->willReturn([$storeId]);
        $storeAdvancedConfig->expects($this->once())
            ->method('setStore')
            ->with($storeId);
        $storeAdvancedConfig->expects($this->once())
            ->method('getProductsSyncBatchSize')
            ->willReturn(2000);
        $entitiesSync->expects($this->once())
            ->method('filterEntitiesYetToSync')
            ->with($productIds, $storeId, EntitiesSync::ENTITY_TYPE_PRODUCT)
            ->willReturn($productIds);
        $queueManager->expects($this->once())
            ->method('getLatestInQueueByClass')
            ->with(IndexProductsProcessor::class, $storeId)
            ->willReturn(null);
        $queueManager->expects($this->once())
            ->method('enqueue')
            ->with(IndexProductsProcessor::class, $storeId, ['products' => $productIds]);
        $entitiesSync->expects($this->once())
            ->method('addEntitiesToSync')
            ->with($productIds, $storeId, EntitiesSync::ENTITY_TYPE_PRODUCT);
        $output->expects($this->once())->method('writeDiv');
        $output->expects($this->once())->method('writeln');

        $subject = new Products(
            $productsManager,
            $queueManager,
            $entitiesSync,
            $storeManager,
            $productPricesHelper,
            $output,
            $storeAdvancedConfig
        );
        $subject->setStoreId($storeId);

        $subject->executeList($productIds);
    }

    public function testExecuteListWithoutExplicitStoreRetainsBroadStoreLookup(): void
    {
        $productsManager = $this->createMock(ProductsManager::class);
        $queueManager = $this->createMock(QueueManager::class);
        $entitiesSync = $this->createMock(EntitiesSync::class);
        $storeManager = $this->createMock(StoreManager::class);
        $productPricesHelper = $this->createMock(ProductPricesHelper::class);
        $output = $this->createMock(IndexerOutput::class);
        $storeAdvancedConfig = $this->createMock(StoreAdvancedConfig::class);

        $storeManager->expects($this->once())
            ->method('getToSyncStoreIds')
            ->with(null)
            ->willReturn([]);
        $queueManager->expects($this->never())->method('enqueue');

        $subject = new Products(
            $productsManager,
            $queueManager,
            $entitiesSync,
            $storeManager,
            $productPricesHelper,
            $output,
            $storeAdvancedConfig
        );

        $subject->executeList([11]);
    }
}
