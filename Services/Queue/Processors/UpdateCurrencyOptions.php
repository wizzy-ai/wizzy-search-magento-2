<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\API\Wizzy\Modules\Currency;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Indexer\IndexerOutput;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class UpdateCurrencyOptions extends QueueProcessorBase
{

    private $storeManager;
    private $currencyManager;
    private $currencyUpdater;
    private $storeGeneralConfig;
    private $output;

    public function __construct(
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        CurrencyManager $currencyManager,
        Currency $currency,
        IndexerOutput $output
    ) {
        $this->storeManager = $storeManager;
        $this->currencyManager = $currencyManager;
        $this->currencyUpdater = $currency;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->output = $output;
    }

    public function execute(array $data, $storeId)
    {
        $storeIds = $this->storeManager->getToSyncStoreIds($storeId);

        foreach ($storeIds as $storeId) {
            $this->storeGeneralConfig->setStore($storeId);
            $this->output->writeln(__('Processing currencies for Store #' . $storeId));

            if (!$this->storeGeneralConfig->isSyncEnabled()) {
                $this->output->writeln(__('Update Currencies Skipped as Sync is disabled.'));
                return true;
            }

            $defaultCurrency = $this->currencyManager->getDefaultCurrency($storeId);
            $displayCurrency = $this->currencyManager->getDisplayCurrency($storeId);
            $supportedCurrencyCodes = $this->currencyManager->getSupportedCurrencies($storeId);
            $supportedCurrencies = $this->getCurrencyDetails($supportedCurrencyCodes);
            $currenciesToDelete = $this->getCurrenciesToDelete($supportedCurrencyCodes, $storeId);

            if (count($supportedCurrencies)) {
                $this->output->writeln(__('Saving ' . count($supportedCurrencies) . ' currencies.'));
                $response = $this->currencyUpdater->save($supportedCurrencies, $storeId);

                if ($response) {
                    $this->output->writeln(__('Saved ' . count($supportedCurrencies) . ' currencies successfully.'));
                }
            }

            if ($defaultCurrency) {
                $this->output->writeln(__('Setting default currency.'));
                $response = $this->currencyUpdater->saveDefaultCurrency($defaultCurrency, $storeId);

                if ($response) {
                    $this->output->writeln(__('Saved default currency.'));
                }
            }
            if ($displayCurrency) {
                $this->output->writeln(__('Setting display currency.'));
                $response = $this->currencyUpdater->saveDisplayCurrency($displayCurrency, $storeId);

                if ($response) {
                    $this->output->writeln(__('Saved display currency.'));
                }
            }

            if (count($currenciesToDelete)) {
                $this->output->writeln(__('Deleting ' . count($currenciesToDelete) . ' currencies.'));
                $response = $this->currencyUpdater->delete($currenciesToDelete, $storeId);

                if ($response) {
                    $this->output->writeln(__('Deleted ' . count($currenciesToDelete) . ' currencies successfully.'));
                }
            }
        }

        return true;
    }

    private function getCurrenciesToDelete(array $supportedCurrencies, $storeId)
    {
        $wizzyCurrencies = $this->currencyUpdater->get($storeId);
        if ($wizzyCurrencies['status'] === true && count($wizzyCurrencies['data']) > 0) {
            $wizzyCurrencies = $wizzyCurrencies['data'];

            $wizzyCurrencies = array_column($wizzyCurrencies, "code");
            $currencies = array_values(array_diff($wizzyCurrencies, $supportedCurrencies));
            $currenciesToDelete = [];

            foreach ($currencies as $currency) {
                $currenciesToDelete[] = [
                'code' => $currency,
                ];
            }

            return $currenciesToDelete;
        }

        return [];
    }

    private function getCurrencyDetails(array $supportedCurrencies)
    {
        $currencies = [];

        foreach ($supportedCurrencies as $supportedCurrency) {
            $currency = $this->currencyManager->getCurrencyByCode($supportedCurrency);
            $symbol = $currency->getSymbol();
            $currencies[] = [
            'code' => $supportedCurrency,
            'label' => $currency->getName(),
            'symbol' => ($symbol) ? $symbol : $supportedCurrency,
            ];
        }

        return $currencies;
    }
}
