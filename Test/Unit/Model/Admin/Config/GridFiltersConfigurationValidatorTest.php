<?php

namespace Wizzy\Search\Test\Unit\Model\Admin\Config;

use Magento\Framework\Exception\LocalizedException;
use PHPUnit\Framework\TestCase;
use Wizzy\Search\Model\Admin\Config\GridFiltersConfigurationValidator;

class GridFiltersConfigurationValidatorTest extends TestCase
{
    public function testNormalizesRowsForConcreteFacetsOnly(): void
    {
        $question = '  Choose a material  ';

        $this->assertSame(
            [
                [
                    'key' => 'material',
                    'question' => $question,
                    'after_web' => 2,
                    'after_mobile' => 3,
                ],
            ],
            $this->createValidator()->normalize(
                [
                    [
                        'key' => 'material',
                        'question' => $question,
                        'after_web' => '2',
                        'after_mobile' => 3,
                    ],
                ],
                $this->facetsConfiguration(['categories', 'material'])
            )
        );
    }

    /**
     * @dataProvider invalidPositionProvider
     */
    public function testRejectsNonCanonicalPositions(string $field, $position): void
    {
        $row = $this->validRow();
        $row[$field] = $position;

        $this->expectException(LocalizedException::class);
        $this->createValidator()->normalize([$row], $this->facetsConfiguration(['categories']));
    }

    public function invalidPositionProvider(): array
    {
        return [
            'zero web string' => ['after_web', '0'],
            'zero mobile integer' => ['after_mobile', 0],
            'leading zero' => ['after_web', '002'],
            'leading whitespace' => ['after_web', ' 2'],
            'trailing whitespace' => ['after_mobile', '2 '],
            'decimal web string' => ['after_web', '1.5'],
            'decimal mobile value' => ['after_mobile', 1.5],
            'negative web integer' => ['after_web', -1],
            'empty mobile string' => ['after_mobile', ''],
        ];
    }

    /**
     * @dataProvider unsupportedKeyProvider
     */
    public function testRejectsPseudoAndNonConfiguredKeys($key): void
    {
        $row = $this->validRow();
        $row['key'] = $key;

        $this->expectException(LocalizedException::class);
        $this->createValidator()->normalize(
            [$row],
            $this->facetsConfiguration(['categories', 'all', 'attributes'])
        );
    }

    public function unsupportedKeyProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace' => ['  '],
            'aggregate all' => ['all'],
            'aggregate attributes' => ['attributes'],
            'grid option not configured as a facet' => ['brands'],
            'unsupported' => ['not_a_supported_filter'],
        ];
    }

    /**
     * @dataProvider invalidQuestionProvider
     */
    public function testRejectsEmptyOrNonTextQuestions($question): void
    {
        $row = $this->validRow();
        $row['question'] = $question;

        $this->expectException(LocalizedException::class);
        $this->createValidator()->normalize([$row], $this->facetsConfiguration(['categories']));
    }

    public function invalidQuestionProvider(): array
    {
        return [
            'empty' => [''],
            'whitespace' => ['  '],
            'integer' => [1],
            'boolean' => [true],
            'array' => [[]],
        ];
    }

    public function testRejectsDuplicateWebPositionsOnly(): void
    {
        $this->expectException(LocalizedException::class);
        $this->createValidator()->normalize(
            [
                $this->validRow('categories', 1, 1),
                $this->validRow('brands', 1, 2),
            ],
            $this->facetsConfiguration(['categories', 'brands'])
        );
    }

    public function testRejectsDuplicateMobilePositionsOnly(): void
    {
        $this->expectException(LocalizedException::class);
        $this->createValidator()->normalize(
            [
                $this->validRow('categories', 1, 1),
                $this->validRow('brands', 2, 1),
            ],
            $this->facetsConfiguration(['categories', 'brands'])
        );
    }

    public function testAllowsTheSamePositionInDifferentViewports(): void
    {
        $this->assertCount(
            2,
            $this->createValidator()->normalize(
                [
                    $this->validRow('categories', 1, 2),
                    $this->validRow('brands', 2, 1),
                ],
                $this->facetsConfiguration(['categories', 'brands'])
            )
        );
    }

    private function createValidator(): GridFiltersConfigurationValidator
    {
        return new GridFiltersConfigurationValidator();
    }

    private function facetsConfiguration(array $keys): array
    {
        return array_map(
            function (string $key): array {
                return ['key' => $key];
            },
            $keys
        );
    }

    private function validRow(string $key = 'categories', $web = 1, $mobile = 1): array
    {
        return [
            'key' => $key,
            'question' => 'Category',
            'after_web' => $web,
            'after_mobile' => $mobile,
        ];
    }
}
