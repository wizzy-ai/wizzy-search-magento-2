<?php

namespace Wizzy\Search\Test\Unit\Model\Observer\AdminConfigs;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Admin\Config\GridFiltersConfigurationValidator;
use Wizzy\Search\Model\Observer\AdminConfigs\CopyConfigurationOptionUpdated;
use Wizzy\Search\Services\Store\ConfigManager;
use Wizzy\Search\Services\Store\StoreCopyConfig;
use Wizzy\Search\Services\Store\StoreSearchConfig;

class CopyConfigurationOptionUpdatedTest extends TestCase
{
    public function testCopyValidatesGridFiltersAgainstSourceStoreFacetsBeforeSaving(): void
    {
        $inputConfiguration = json_encode([
            [
                'key' => 'categories',
                'question' => 'Choose a category',
                'after_web' => '2',
                'after_mobile' => '3',
            ],
        ]);
        $savedConfigurations = [];
        $observer = $this->createObserver(
            $inputConfiguration,
            $this->facetsConfiguration(['categories']),
            $savedConfigurations
        );

        $observer->copyConfiguration();

        $this->assertSame(
            json_encode([
                [
                    'key' => 'categories',
                    'question' => 'Choose a category',
                    'after_web' => 2,
                    'after_mobile' => 3,
                ],
            ]),
            $savedConfigurations[StoreSearchConfig::WIZZY_GRID_FILTERS_CONFIGURATION]
        );
    }

    public function testCopyRejectsGridOnlyKeyMissingFromSourceStoreFacets(): void
    {
        $savedConfigurations = [];
        $observer = $this->createObserver(
            json_encode([
                [
                    'key' => 'brands',
                    'question' => 'Choose a brand',
                    'after_web' => '1',
                    'after_mobile' => '3',
                ],
            ]),
            $this->facetsConfiguration(['categories']),
            $savedConfigurations
        );

        try {
            $observer->copyConfiguration();
            $this->fail('Expected the copy to reject a grid-only field.');
        } catch (LocalizedException $exception) {
            $this->assertSame([], $savedConfigurations);
        }
    }

    private function createObserver(
        string $gridFiltersConfiguration,
        string $facetsConfiguration,
        array &$savedConfigurations
    ): CopyConfigurationOptionUpdated {
        $request = $this->createMock(Http::class);
        $request->method('getParam')->with('store')->willReturn('target-store');

        $configManager = $this->createMock(ConfigManager::class);
        $configManager->method('getStoreConfig')->willReturnCallback(
            function ($path) use ($gridFiltersConfiguration, $facetsConfiguration) {
                if ($path === StoreSearchConfig::WIZZY_GRID_FILTERS_CONFIGURATION) {
                    return $gridFiltersConfiguration;
                }

                if ($path === StoreSearchConfig::WIZZY_FACETS) {
                    return $facetsConfiguration;
                }

                return null;
            }
        );

        $configWriter = $this->createMock(WriterInterface::class);
        $configWriter->method('save')->willReturnCallback(
            function ($path, $value) use (&$savedConfigurations) {
                $savedConfigurations[$path] = $value;
            }
        );

        $cacheTypeList = $this->createMock(TypeListInterface::class);
        $cacheTypeList->method('cleanType');

        $storeCopyConfig = $this->createMock(StoreCopyConfig::class);
        $storeCopyConfig->method('getCopyConfigFrom')->willReturn('source-store');

        return new CopyConfigurationOptionUpdated(
            $request,
            $configManager,
            $configWriter,
            $cacheTypeList,
            $storeCopyConfig,
            new GridFiltersConfigurationValidator()
        );
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
