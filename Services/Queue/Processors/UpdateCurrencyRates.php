<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\API\Wizzy\Modules\CurrencyRate;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class UpdateCurrencyRates extends QueueProcessorBase
{

    private $storeManager;
    private $currencyManager;
    private $currencyRateUpdater;
    private $storeGeneralConfig;
    private $output;

    public function __construct(
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        CurrencyManager $currencyManager,
        CurrencyRate $currencyRate,
        IndexerOutput $output
    ) {
        $this->storeManager = $storeManager;
        $this->currencyManager = $currencyManager;
        $this->currencyRateUpdater = $currencyRate;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->output = $output;
    }

    public function execute(array $data, $storeId)
    {
        $storeIds = $this->storeManager->getToSyncStoreIds($storeId);

        foreach ($storeIds as $storeId) {
            $this->storeGeneralConfig->setStore($storeId);
            $this->output->writeln(__('Processing currency rates for Store #' . $storeId));

            if (!$this->storeGeneralConfig->isSyncEnabled()) {
                $this->output->writeln(__('Update Currency Rates Skipped as Sync is disabled.'));
                return true;
            }

            $currencyRates = $this->getCurrencyRates($storeId);
            if ($currencyRates) {
                $this->output->writeln(__('Saving ' . count($currencyRates) . ' currency rates.'));

                $response = $this->currencyRateUpdater->save($currencyRates, $storeId);
                if ($response) {
                    $this->output->writeln(__('Saved ' . count($currencyRates) . ' currency rates successfully.'));
                }
            } else {
                $this->output->writeln(__('No currency rates to be saved'));
            }
        }

        return true;
    }

    private function getCurrencyRates($storeId)
    {
        $code = $this->currencyManager->getDefaultCurrency($storeId);
        $currencyRates = $this->currencyManager->getCurrencyRates($storeId, $code);
        $currencyRatesToPush = [];

        foreach ($currencyRates as $targetCode => $currencyRate) {
            if ($currencyRate) { // Skipping the rates which are not set yet.
                $currencyRatesToPush[] = [
                 'sourceCode' => $code,
                 'targetCode' => $targetCode,
                 'rate' => floatval($currencyRate)
                ];
            }
        }

        return $currencyRatesToPush;
    }
}
