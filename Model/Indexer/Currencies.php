<?php
namespace Wizzy\Search\Model\Indexer;

use Wizzy\Search\Services\Config\CurrencyOptions;
use Magento;
use Wizzy\Search\Services\Store\StoreManager;

class Currencies implements Magento\Framework\Indexer\ActionInterface, Magento\Framework\Mview\ActionInterface
{

    private $currencyOptions;

    public function __construct(CurrencyOptions $currencyOptions, StoreManager $storeManager)
    {
        $this->currencyOptions = $currencyOptions;
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
