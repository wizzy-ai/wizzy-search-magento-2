<?php

namespace Wizzy\Search\Test\Unit\Services\Store;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Admin\Config\GridFiltersConfigurationValidator;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreSearchConfig;

class StoreSearchConfigTest extends TestCase
{
    public function testSkipsInvalidStoredGridFiltersWhenDisabled(): void
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->expects($this->once())
            ->method('getStoreConfig')
            ->with(StoreSearchConfig::WIZZY_GRID_FILTERS_ENABLED, '2')
            ->willReturn('0');
        $storeSearchConfig = new StoreSearchConfig($configManager, $this->createValidator());
        $storeSearchConfig->setStore('2');

        $this->assertSame(
            [
                'enabled' => false,
                'filters' => [],
            ],
            $storeSearchConfig->getGridFiltersConfiguration()
        );
    }

    public function testEmitsGridFiltersOnlyForEffectiveStoredFacets(): void
    {
        $configuration = json_encode([
            [
                'key' => 'categories',
                'question' => 'Choose a category',
                'after_web' => '2',
                'after_mobile' => '3',
            ],
        ]);
        $configManager = $this->createConfigManager($configuration, $this->facetsConfiguration(['categories']));
        $storeSearchConfig = new StoreSearchConfig($configManager, $this->createValidator());
        $storeSearchConfig->setStore('2');

        $this->assertSame(
            [
                'enabled' => true,
                'filters' => [
                    [
                        'key' => 'categories',
                        'question' => 'Choose a category',
                        'after' => [
                            'web' => 2,
                            'mobile' => 3,
                        ],
                    ],
                ],
            ],
            $storeSearchConfig->getGridFiltersConfiguration()
        );
    }

    public function testRejectsGridOnlyKeyThatIsNotConfiguredAsAFacet(): void
    {
        $configManager = $this->createConfigManager(
            json_encode([
                [
                    'key' => 'brands',
                    'question' => 'Choose a brand',
                    'after_web' => '1',
                    'after_mobile' => '3',
                ],
            ]),
            $this->facetsConfiguration(['categories'])
        );
        $storeSearchConfig = new StoreSearchConfig($configManager, $this->createValidator());
        $storeSearchConfig->setStore('2');

        $this->expectException(LocalizedException::class);
        $storeSearchConfig->getGridFiltersConfiguration();
    }

    public function testDoesNotCoerceNonCanonicalPositionsInTheEmittedPayload(): void
    {
        $configManager = $this->createConfigManager(
            json_encode([
                [
                    'key' => 'categories',
                    'question' => 'Choose a category',
                    'after_web' => '02',
                    'after_mobile' => '3',
                ],
            ]),
            $this->facetsConfiguration(['categories'])
        );
        $storeSearchConfig = new StoreSearchConfig($configManager, $this->createValidator());
        $storeSearchConfig->setStore('2');

        $this->expectException(LocalizedException::class);
        $storeSearchConfig->getGridFiltersConfiguration();
    }

    private function createConfigManager(string $gridFiltersConfiguration, string $facetsConfiguration): ConfigManager
    {
        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('getStoreConfig')->willReturnCallback(
            function ($path) use ($gridFiltersConfiguration, $facetsConfiguration) {
                if ($path === StoreSearchConfig::WIZZY_GRID_FILTERS_ENABLED) {
                    return '1';
                }

                if ($path === StoreSearchConfig::WIZZY_GRID_FILTERS_CONFIGURATION) {
                    return $gridFiltersConfiguration;
                }

                if ($path === StoreSearchConfig::WIZZY_FACETS) {
                    return $facetsConfiguration;
                }

                return null;
            }
        );

        return $configManager;
    }

    private function createValidator(): GridFiltersConfigurationValidator
    {
        return new GridFiltersConfigurationValidator();
    }

    private function facetsConfiguration(array $keys): string
    {
        return json_encode(array_map(
            function (string $key): array {
                return ['key' => $key];
            },
            $keys
        ));
    }
}
