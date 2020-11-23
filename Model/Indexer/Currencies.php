<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Config\CurrencyOptions;
use Magento;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Store\StoreManager;

class Currencies implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{

    private $currencyOptions;
    private $output;

    public function __construct(CurrencyOptions $currencyOptions, IndexerOutput $output)
    {
        $this->currencyOptions = $currencyOptions;
        $this->output = $output;
    }

   /*
    * No need to execute anything.
    */
    public function execute($ids)
    {
        return $this;
    }

   /*
    * Execute Currencies Indexer
    */
    public function executeFull()
    {
        $this->currencyOptions->onOptionsUpdated();
        $this->output->writeDiv();
        $this->output->writeln(__('Added Currencies for Sync.'));

        return $this;
    }

   /*
    * Execute Currencies Indexer
    */
    public function executeList(array $ids)
    {
        $this->currencyOptions->onOptionsUpdated();
        return $this;
    }

   /*
    * Execute Currencies Indexer
    */
    public function executeRow($id)
    {
        $this->currencyOptions->onOptionsUpdated();
        return $this;
    }
}
