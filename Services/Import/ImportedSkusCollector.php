<?php
declare(strict_types=1);

namespace Wizzy\Search\Services\Import;

/**
 * Collects SKUs during an import run.
 *
 * This is intentionally non-static so it remains interceptable and follows Magento DI practices.
 * The same instance will be shared within the same request/process via Magento's object manager.
 */
class ImportedSkusCollector
{
    /**
     * @var array<string, bool>
     */
    private $skus = [];

    /**
     * @param string[] $skus
     */
    public function addSkus(array $skus): void
    {
        foreach ($skus as $sku) {
            if ($sku === null || $sku === '') {
                continue;
            }
            $this->skus[(string)$sku] = true;
        }
    }

    /**
     * @return string[]
     */
    public function getSkus(): array
    {
        return array_keys($this->skus);
    }

    public function clear(): void
    {
        $this->skus = [];
    }
}
