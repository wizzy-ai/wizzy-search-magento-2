<?php

namespace Wizzy\Search\Test\Unit\Model\Admin\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Admin\Config\Backend\GridFiltersConfiguration;
use Wizzy\Search\Model\Admin\Config\GridFiltersConfigurationValidator;
use Wizzy\Search\Services\Store\StoreSearchConfig;

class GridFiltersConfigurationTest extends TestCase
{
    public function testCanBeConstructedWithMagentoArraySerializedArgumentOrder(): void
    {
        $backend = $this->createBackend($this->createMock(ScopeConfigInterface::class));

        $this->assertInstanceOf(GridFiltersConfiguration::class, $backend);
    }

    public function testUsesSameSaveFacetsPayloadBeforePersistingGridFilters(): void
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->expects($this->never())->method('getValue');
        $backend = $this->createBackend($config);
        $backend->setData('fieldset_data', [
            'facets_configuration' => [
                ['key' => 'categories'],
            ],
        ]);
        $backend->setValue([
            [
                'key' => 'categories',
                'question' => 'Choose a category',
                'after_web' => '2',
                'after_mobile' => '3',
            ],
        ]);

        $backend->beforeSave();

        $this->assertSame(
            json_encode([
                [
                    'key' => 'categories',
                    'question' => 'Choose a category',
                    'after_web' => 2,
                    'after_mobile' => 3,
                ],
            ]),
            $backend->getValue()
        );
    }

    public function testUsesEffectiveStoredFacetsWhenThePeerFieldIsNotSubmitted(): void
    {
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->expects($this->once())->method('getValue')->with(
            StoreSearchConfig::WIZZY_FACETS,
            'stores',
            'store-code'
        )->willReturn(json_encode([
            ['key' => 'categories'],
        ]));
        $backend = $this->createBackend($config);
        $backend->setScope('stores');
        $backend->setScopeCode('store-code');
        $backend->setValue([
            [
                'key' => 'categories',
                'question' => 'Choose a category',
                'after_web' => 2,
                'after_mobile' => 3,
            ],
        ]);

        $backend->beforeSave();

        $this->assertNotEmpty($backend->getValue());
    }

    public function testRejectsScalarSubmissionWithoutReplacingItWithAnEmptyConfiguration(): void
    {
        $backend = $this->createBackend($this->createMock(ScopeConfigInterface::class));
        $backend->setValue('malformed submission');

        try {
            $backend->beforeSave();
            $this->fail('Expected malformed scalar grid filters configuration to be rejected.');
        } catch (LocalizedException $exception) {
            $this->assertSame('Grid Filters configuration must be an array.', $exception->getMessage());
        }

        $this->assertSame('malformed submission', $backend->getValue());
    }

    private function createBackend(ScopeConfigInterface $config): GridFiltersConfiguration
    {
        $serializer = $this->createMock(Json::class);
        $serializer->method('serialize')->willReturnCallback(
            function ($value): string {
                return json_encode($value);
            }
        );

        return new GridFiltersConfiguration(
            $this->createMock(Context::class),
            $this->createMock(Registry::class),
            $config,
            $this->createMock(TypeListInterface::class),
            $this->createMock(AbstractResource::class),
            $this->createMock(AbstractDb::class),
            [],
            $serializer,
            new GridFiltersConfigurationValidator()
        );
    }
}
