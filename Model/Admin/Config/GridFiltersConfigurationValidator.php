<?php

namespace Wizzy\Search\Model\Admin\Config;

use Magento\Framework\Exception\LocalizedException;

class GridFiltersConfigurationValidator
{
    private const AGGREGATE_KEYS = [
        'all' => true,
        'attributes' => true,
    ];

    /**
     * Validate grid-filter rows and return the canonical configuration format.
     *
     * @param array $rows
     * @param array $facetsConfiguration
     * @return array
     * @throws LocalizedException
     */
    public function normalize(array $rows, array $facetsConfiguration): array
    {
        $supportedKeys = $this->getSupportedKeys($facetsConfiguration);
        $webPositions = [];
        $mobilePositions = [];
        $normalizedRows = [];

        foreach ($rows as $rowId => $row) {
            // Magento's dynamic-row component adds this marker to submitted values.
            if ($rowId === '__empty') {
                continue;
            }

            if (!is_array($row)) {
                throw new LocalizedException(__('Each Grid Filters row must be an array.'));
            }

            $key = $this->normalizeKey($row);
            if (!isset($supportedKeys[$key])) {
                throw new LocalizedException(__('Grid Filters contains an unsupported field "%1".', $key));
            }

            $afterWeb = $this->normalizePosition($row['after_web'] ?? null, 'After (Web)');
            $afterMobile = $this->normalizePosition($row['after_mobile'] ?? null, 'After (Mobile)');

            if (isset($webPositions[$afterWeb])) {
                throw new LocalizedException(
                    __('Grid Filters contains more than one row at web position %1.', $afterWeb)
                );
            }
            if (isset($mobilePositions[$afterMobile])) {
                throw new LocalizedException(
                    __('Grid Filters contains more than one row at mobile position %1.', $afterMobile)
                );
            }

            $webPositions[$afterWeb] = true;
            $mobilePositions[$afterMobile] = true;

            $normalizedRows[] = [
                'key' => $key,
                'question' => $this->normalizeQuestion($row['question'] ?? ''),
                'after_web' => $afterWeb,
                'after_mobile' => $afterMobile,
            ];
        }

        return $normalizedRows;
    }

    private function getSupportedKeys(array $facetsConfiguration): array
    {
        $supportedKeys = [];

        foreach ($facetsConfiguration as $facet) {
            if (!is_array($facet) || !isset($facet['key']) || !is_string($facet['key'])) {
                continue;
            }

            $key = $facet['key'];
            if ($key !== '' && !isset(self::AGGREGATE_KEYS[$key])) {
                $supportedKeys[$key] = true;
            }
        }

        return $supportedKeys;
    }

    /**
     * @param array $row
     * @return string
     * @throws LocalizedException
     */
    private function normalizeKey(array $row): string
    {
        if (!isset($row['key']) || !is_string($row['key']) || trim($row['key']) === '') {
            throw new LocalizedException(__('Each Grid Filters row must have a field.'));
        }

        return $row['key'];
    }

    /**
     * @param mixed $value
     * @param string $label
     * @return int
     * @throws LocalizedException
     */
    private function normalizePosition($value, string $label): int
    {
        if (is_int($value)) {
            if ($value > 0) {
                return $value;
            }

            throw new LocalizedException(__('%1 must be a positive integer.', $label));
        }

        if (!is_string($value)) {
            throw new LocalizedException(__('%1 must be a positive integer.', $label));
        }

        if (preg_match('/^[1-9][0-9]*\\z/', $value) !== 1 || (string) (int) $value !== $value) {
            throw new LocalizedException(__('%1 must be a positive integer.', $label));
        }

        return (int) $value;
    }

    /**
     * @param mixed $question
     * @return string
     * @throws LocalizedException
     */
    private function normalizeQuestion($question): string
    {
        if (!is_string($question) || trim($question) === '') {
            throw new LocalizedException(__('Grid Filters questions must be non-empty text.'));
        }

        return $question;
    }
}
