<?php

namespace Wizzy\Search\Services\Queue\Processors;

use Wizzy\Search\Services\API\Wizzy\Modules\Currency;
use Wizzy\Search\Services\Currency\CurrencyManager;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;

class UpdateCurrencyOptions extends QueueProcessorBase
{

    private $storeManager;
    private $currencyManager;
    private $currencyUpdater;
    private $storeGeneralConfig;

    public function __construct(
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        CurrencyManager $currencyManager,
        Currency $currency
    ) {
        $this->storeManager = $storeManager;
        $this->currencyManager = $currencyManager;
        $this->currencyUpdater = $currency;
        $this->storeGeneralConfig = $storeGeneralConfig;
    }

    public function execute(array $data, $storeId)
    {

        if (!$this->storeGeneralConfig->isSyncEnabled()) {
            return true;
        }

        $storeIds = $this->storeManager->getToSyncStoreIds($storeId);

        foreach ($storeIds as $storeId) {
            $defaultCurrency = $this->currencyManager->getDefaultCurrency($storeId);
            $displayCurrency = $this->currencyManager->getDisplayCurrency($storeId);
            $supportedCurrencyCodes = $this->currencyManager->getSupportedCurrencies($storeId);
            $supportedCurrencies = $this->getCurrencyDetails($supportedCurrencyCodes);
            $currenciesToDelete = $this->getCurrenciesToDelete($supportedCurrencyCodes, $storeId);

            if ($defaultCurrency) {
                $this->currencyUpdater->saveDefaultCurrency($defaultCurrency, $storeId);
            }
            if ($displayCurrency) {
                $this->currencyUpdater->saveDisplayCurrency($displayCurrency, $storeId);
            }

            if (count($supportedCurrencies)) {
                $this->currencyUpdater->save($supportedCurrencies, $storeId);
            }
            if (count($currenciesToDelete)) {
                $this->currencyUpdater->delete($currenciesToDelete, $storeId);
            }
        }

        return true;
    }

    private function getCurrenciesToDelete(array $supportedCurrencies, $storeId)
    {
        $wizzyCurrencies = $this->currencyUpdater->get($storeId);
        if ($wizzyCurrencies !== false && count($wizzyCurrencies) > 0) {
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
            $currencies[] = [
            'code' => $supportedCurrency,
            'label' => $currency->getName(),
            'symbol' => $currency->getSymbol(),
            ];
        }

        return $currencies;
    }
}
