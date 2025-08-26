<?php

namespace Wizzy\Search\Model\Observer\AdminConfigs;

use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;
use Wizzy\Search\Services\Store\ConfigManager;
use Magento\Framework\Event\ObserverInterface;
use Wizzy\Search\Services\Store\StoreCopyConfig;
use Wizzy\Search\Services\Store\StoreSearchConfig;
use Magento\Framework\App\Cache\TypeListInterface;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreCatalogueConfig;
use Wizzy\Search\Services\Store\StoreSearchFormConfig;
use Magento\Framework\Event\Observer as EventObserver;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Magento\Framework\App\Config\Storage\WriterInterface;

class CopyConfigurationOptionUpdated implements ObserverInterface
{
    private $request;
    private $configManager;
    private $_configWriter;
    private $cacheTypeList;
    private $storeCopyConfig;

    public function __construct(
        Http $request,
        ConfigManager $configManager,
        WriterInterface $configWriter,
        TypeListInterface $cacheTypeList,
        StoreCopyConfig $storeCopyConfig
    ) {
        $this->request = $request;
        $this->_configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->configManager = $configManager;
        $this->storeCopyConfig = $storeCopyConfig;
    }

    public function copyConfiguration()
    {
        $fromStoreId = $this->storeCopyConfig->getCopyConfigFrom();
        $toStoreId = $this->request->getParam('store');
        $scopeType = ScopeInterface::SCOPE_STORES;
        $configPath = [
            StoreCatalogueConfig::IS_MULTI_GENDER_STORE,
            StoreCatalogueConfig::GENDER_IDENTIFIABLE_BY,
            StoreCatalogueConfig::GENDER_IDENTIFIABLE_CATEGORIES,
            StoreCatalogueConfig::GENDER_IDENTIFIABLE_ATTRIBUTES,
            StoreCatalogueConfig::IS_MULTI_BRAND_STORE,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_BY,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_CATEGORIES_WAY,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_CATEGORIES_LEVEL,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_CATEGORIES_SELECTION,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_SUB_CATEGORIES_SELECTION,
            StoreCatalogueConfig::BRAND_IDENTIFIABLE_ATTRIBUTE_SELECTION,
            StoreCatalogueConfig::BRAND_IS_MANDATORY_FOR_SYNC,
            StoreCatalogueConfig::IS_COLORS_VARIABLE_PRODUCTS,
            StoreCatalogueConfig::COLORS_IDENTIFIABLE_ATTRIBUTES,
            StoreCatalogueConfig::IS_SIZES_VARIABLE_PRODUCTS,
            StoreCatalogueConfig::SIZES_IDENTIFIABLE_ATTRIBUTES,
            StoreCatalogueConfig::IGNORE_DESCRIPTION,
            StoreCatalogueConfig::COMMON_WORDS_TO_REMOVE,
            StoreCatalogueConfig::HAS_TO_USE_MSRP_AS_ORIGINAL_PRICE,
            StoreCatalogueConfig::THUMBNAIL_IMAGE_WIDTH,
            StoreCatalogueConfig::THUMBNAIL_IMAGE_HEIGHT,
            StoreCatalogueConfig::REPLACE_CHILD_IMAGE,
            StoreCatalogueConfig::REPLACE_CHILD_NAME,
            StoreCatalogueConfig::HOVER_IMAGE_TYPE,
            StoreCatalogueConfig::THUMBNAIL_IMAGE_TYPE,
            StoreCatalogueConfig::IS_USING_INVENTORY_MANAGEMENT,
            StoreCatalogueConfig::INVENTORY_SOURCE_LIST,
            StoreSearchFormConfig::WIZZY_SEARCH_INPUT_PLACEHOLDER,
            StoreSearchConfig::WIZZY_DOM_SELECTOR,
            StoreSearchConfig::WIZZY_SEARCH_ENDPOINT,
            StoreSearchConfig::WIZZY_NO_OF_PRODUCTS,
            StoreSearchConfig::WIZZY_DISPLAY_ADD_TO_CART_BUTTON,
            StoreSearchConfig::WIZZY_DISPLAY_ADD_TO_WISHLIST_BUTTON,
            StoreSearchConfig::WIZZY_FACETS,
            StoreSearchConfig::WIZZY_FACET_CATEGORY_DISPLAY,
            StoreSearchConfig::WIZZY_LEFT_FACETS_COLLAPSIBLE,
            StoreSearchConfig::WIZZY_LEFT_FACETS_DEFAULT_COLLAPSIBLE_BEHAVIOUR,
            StoreSearchConfig::WIZZY_LEFT_FIRST_FACET_DEFAULT_COLLAPSIBLE_BEHAVIOUR,
            StoreSearchConfig::WIZZY_PAGINATION_TYPE,
            StoreSearchConfig::WIZZY_PAGINATION_MOVE_TO_TOP_WIDGET,
            StoreSearchConfig::INFINITE_SCROLL_OFFSET_DESKTOP,
            StoreSearchConfig::INFINITE_SCROLL_OFFSET_MOBILE,
            StoreSearchConfig::WIZZY_SWATCHES_CONFIGURATION,
            StoreSearchConfig::WIZZY_SORT_CONFIGURATION,
            StoreSearchConfig::NO_RESULTS_PAGE_TITLE,
            StoreSearchConfig::SHOW_NO_RESULTS_PRODUCTS_SUGGESTIONS,
            StoreSearchConfig::NO_RESULTS_PRODUCTS_SELECTION,
            StoreSearchConfig::NO_RESULTS_PRODUCTS_COUNT,
            StoreSearchConfig::NO_RESULTS_PAGE_SUB_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_SUGGESTIONS_COUNT,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_CATEGOIRES_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_OTHERS_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_BRANDS_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_SECTIONS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_MENU_ALIGNMENT,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_NO_RESULTS_BEHAVIOUR,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_NO_RESULTS_TEXT,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_SHOW_TOP_PRODUCTS,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_TOP_PRODUCTS_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_TOP_PRODUCTS_COUNT,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_HAS_TO_IGNORE_CATEGORIES,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_CATEGORIES_TO_IGNORE,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_PAGES_TITLE,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_EXCLUDE_PAGES,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_SYNC_PAGES,
            StoreAutocompleteConfig::AUTOCOMPLETE_ENABLED_ATTRIBUTES,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_SHOW_DEFAULT_SUGGESTIONS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_DISPLAY_RECENTLY_SEARCHED_TERMS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_SHOW_PINNED_TERMS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_PINNED_TERM_SELECTION,
            StoreAutocompleteConfig::WIZZY_AUTTOCOMPLETE_SHOW_DEFAULT_PRODUCTS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_SHOW_PINNED_PRODUCTS,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_PINNED_PRODUCTS_SELECTION,
            StoreAutocompleteConfig::WIZZY_AUTOCOMPLETE_PINNED_PRODUCTS_COUNT,
            StoreAdvancedConfig::INCLUDE_CUSTOM_CSS,
            StoreAdvancedConfig::TEMPLATE_ATTRIBUTES,
            StoreAdvancedConfig::PRODUCTS_SYNC_BATCH_SIZE,
            StoreAdvancedConfig::SYNC_DEQUEUE_SIZE,
            StoreAdvancedConfig::HAS_TO_ADD_PRODUCTS_IN_SYNC_ON_ATTRIBUTE_SAVE,
            StoreAdvancedConfig::SYNC_DEBUGGING,
            StoreAdvancedConfig::HAS_TO_ADD_ALL_PRODUCTS_IN_SYNC
        ];

        foreach ($configPath as $path) {
            $oldConfig[$path] = $this->configManager->getStoreConfig($path, $fromStoreId);
        }

        foreach ($oldConfig as $path => $value) {
            $this->_configWriter->save(
                $path,
                $value,
                $scopeType,
                $toStoreId
            );
        }

        $cacheType = 'config';
        $this->cacheTypeList->cleanType($cacheType);
    }

    public function execute(EventObserver $observer)
    {
        return $this->copyConfiguration();
    }
}
