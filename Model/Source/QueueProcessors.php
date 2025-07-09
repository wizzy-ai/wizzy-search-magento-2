<?php

namespace Wizzy\Search\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Wizzy\Search\Services\Queue\Processors\CatalogueReindexer;
use Wizzy\Search\Services\Queue\Processors\IndexCategoryProductsProcessor;
use Wizzy\Search\Services\Queue\Processors\IndexPagesProcessor;
use Wizzy\Search\Services\Queue\Processors\IndexProductsProcessor;
use Wizzy\Search\Services\Queue\Processors\QueueProcessorBase;
use Wizzy\Search\Services\Queue\Processors\UpdateCurrencyOptions;
use Wizzy\Search\Services\Queue\Processors\UpdateCurrencyRates;
use Wizzy\Search\Services\Queue\Processors\AddImportedProductsInQueueProcessor;

class QueueProcessors implements OptionSourceInterface
{
    private $processors = [
      CatalogueReindexer::class => 'Catalogue Reindexer',
      IndexCategoryProductsProcessor::class => 'Index Category Products',
      IndexPagesProcessor::class => 'Index Pages',
      IndexProductsProcessor::class => 'Index Products',
      UpdateCurrencyOptions::class => 'Index Currencies',
      UpdateCurrencyRates::class => 'Index Currency Rates',
      AddImportedProductsInQueueProcessor::class => 'Add Imported Products In Sync',
    ];

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->processors as $processorKey => $value) {
            $options[] = [
            'value' => $processorKey,
            'label' => $value,
            ];
        }
        return $options;
    }

    public function getLabel($processorClass)
    {
        if (isset($this->processors[$processorClass])) {
            return $this->processors[$processorClass];
        }

        return '';
    }
}
