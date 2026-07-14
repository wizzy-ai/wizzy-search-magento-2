<?php

namespace Wizzy\Search\Test\Unit\Model\Observer\AdminConfigs;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Helpers\FlashMessagesManager;
use Wizzy\Search\Model\Observer\AdminConfigs\WizzyCatalogueConfigurationChanged;
use Wizzy\Search\Services\Config\WizzyCatalogueConfiguration;
use Wizzy\Search\Services\Model\EntitiesSync;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\QueueManager;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreManager;

class WizzyCatalogueConfigurationChangedTest extends TestCase
{
    private $request;
    private $messageManager;
    private $configManager;
    private $queueManager;
    private $storeManager;
    private $entitiesSync;
    private $catalogueConfiguration;
    private $observer;
    private $subject;

    protected function setUp(): void
    {
        $this->request = $this->createMock(RequestInterface::class);
        $this->messageManager = $this->createMock(FlashMessagesManager::class);
        $this->configManager = $this->createMock(ConfigManager::class);
        $this->queueManager = $this->createMock(QueueManager::class);
        $this->storeManager = $this->createMock(StoreManager::class);
        $this->entitiesSync = $this->createMock(EntitiesSync::class);
        $this->catalogueConfiguration = $this->createMock(WizzyCatalogueConfiguration::class);
        $this->observer = $this->createMock(Observer::class);

        $this->subject = new WizzyCatalogueConfigurationChanged(
            $this->request,
            $this->messageManager,
            $this->configManager,
            $this->queueManager,
            $this->storeManager,
            $this->entitiesSync,
            $this->catalogueConfiguration
        );
    }

    /**
     * @dataProvider invalidStoreProvider
     */
    public function testInvalidRequestStoreHasNoSideEffects($requestStore, $storeLookupResult): void
    {
        $this->configureRequest($requestStore, ['catalogue' => ['value' => 'changed']]);

        if ($storeLookupResult === 'exception') {
            $this->storeManager->expects($this->once())
                ->method('getStoreById')
                ->with(999)
                ->willThrowException(new NoSuchEntityException());
        } elseif ($storeLookupResult === 'null') {
            $this->storeManager->expects($this->once())
                ->method('getStoreById')
                ->with(998)
                ->willReturn(null);
        } elseif ($storeLookupResult === 'mismatch') {
            $store = $this->createMock(StoreInterface::class);
            $store->expects($this->once())->method('getId')->willReturn(3);
            $this->storeManager->expects($this->once())
                ->method('getStoreById')
                ->with(997)
                ->willReturn($store);
        } else {
            $this->storeManager->expects($this->never())->method('getStoreById');
        }

        $this->storeManager->expects($this->never())->method('getCurrentStoreId');
        $this->configManager->expects($this->never())->method('getCustomStoreConfig');
        $this->configManager->expects($this->never())->method('save');
        $this->configManager->expects($this->never())->method('saveStoreConfig');
        $this->entitiesSync->expects($this->never())->method('hasAnyEntitiesAddedInSync');
        $this->messageManager->expects($this->never())->method('warning');
        $this->catalogueConfiguration->expects($this->never())->method('clearProductIndexingJobs');
        $this->queueManager->expects($this->never())->method('enqueue');

        $this->assertSame($this->subject, $this->subject->execute($this->observer));
    }

    public function testRequestStoreIsUsedForEverySideEffectWhenCurrentStoreDiffers(): void
    {
        $groups = ['catalogue' => ['value' => 'changed']];
        $encodedGroups = json_encode($groups);
        $this->configureRequest(' 2 ', $groups);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getId')->willReturn(2);
        $this->storeManager->expects($this->once())->method('getStoreById')->with(2)->willReturn($store);
        $this->storeManager->expects($this->never())->method('getCurrentStoreId')->willReturn(9);

        $this->configManager->expects($this->once())
            ->method('getCustomStoreConfig')
            ->with(ConfigManager::CATALOGUE_CONFIG, 2)
            ->willReturn('{"catalogue":{"value":"old"}}');
        $this->entitiesSync->expects($this->once())
            ->method('hasAnyEntitiesAddedInSync')
            ->with(2, EntitiesSync::ENTITY_TYPE_PRODUCT)
            ->willReturn(true);
        $this->messageManager->expects($this->once())->method('warning');
        $this->catalogueConfiguration->expects($this->once())
            ->method('clearProductIndexingJobs')
            ->with(2);
        $this->queueManager->expects($this->once())
            ->method('enqueue')
            ->with(CatalogueReindexer::class, 2, [2]);
        $this->configManager->expects($this->once())
            ->method('save')
            ->with(
                ConfigManager::CATALOGUE_CONFIG,
                $encodedGroups,
                ScopeInterface::SCOPE_STORES,
                2
            );
        $this->configManager->expects($this->never())->method('saveStoreConfig');

        $this->assertSame($this->subject, $this->subject->execute($this->observer));
    }

    public static function invalidStoreProvider(): array
    {
        return [
            'missing store' => [null, false],
            'empty store' => ['', false],
            'admin scope' => ['0', false],
            'negative store' => ['-1', false],
            'decimal store' => ['1.5', false],
            'non-numeric store' => ['store-two', false],
            'array store' => [[2], false],
            'boolean store' => [true, false],
            'unknown store throws' => ['999', 'exception'],
            'unknown store returns null' => ['998', 'null'],
            'store lookup ID mismatch' => ['997', 'mismatch'],
        ];
    }

    private function configureRequest($storeId, array $groups): void
    {
        $this->request->expects($this->exactly(2))
            ->method('getParam')
            ->willReturnCallback(function ($name) use ($storeId, $groups) {
                if ($name === 'groups') {
                    return $groups;
                }

                if ($name === 'store') {
                    return $storeId;
                }

                return null;
            });
    }
}
