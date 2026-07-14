<?php

namespace Wizzy\Search\Test\Unit\Services\Queue\Processors;

use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Indexer\Products as ProductsIndexer;
use Wizzy\Search\Services\Catalogue\ProductsManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Store\StoreGeneralConfig;

class CatalogueReindexerTest extends TestCase
{
    public function testQueueStoreIsSetImmediatelyBeforeExecuteList(): void
    {
        $storeId = 7;
        $productIds = [11, 12];
        $calls = [];
        $productsIndexer = $this->getMockBuilder(ProductsIndexer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $storeGeneralConfig = $this->createMock(StoreGeneralConfig::class);
        $entitiesSync = $this->createMock(EntitiesSync::class);
        $productsManager = $this->createMock(ProductsManager::class);
        $output = $this->createMock(IndexerOutput::class);

        $storeGeneralConfig->expects($this->once())->method('setStore')->with($storeId);
        $storeGeneralConfig->expects($this->once())->method('isSyncEnabled')->willReturn(true);
        $entitiesSync->expects($this->once())
            ->method('markAllEntitiesSynced')
            ->with($storeId, EntitiesSync::ENTITY_TYPE_PRODUCT);
        $productsManager->expects($this->once())
            ->method('getAllProductIds')
            ->with($storeId)
            ->willReturn($productIds);
        $output->expects($this->once())->method('writeln');
        $productsIndexer->expects($this->once())
            ->method('setStoreId')
            ->with($storeId)
            ->willReturnCallback(function ($value) use (&$calls) {
                $calls[] = ['setStoreId', $value];
            });
        $productsIndexer->expects($this->once())
            ->method('executeList')
            ->with($productIds)
            ->willReturnCallback(function ($ids) use (&$calls) {
                $calls[] = ['executeList', $ids];
            });

        $subject = new CatalogueReindexer(
            $productsIndexer,
            $storeGeneralConfig,
            $entitiesSync,
            $productsManager,
            $output
        );

        $this->assertTrue($subject->execute([], $storeId));
        $this->assertSame(
            [
                ['setStoreId', $storeId],
                ['executeList', $productIds],
            ],
            $calls
        );
    }
}
