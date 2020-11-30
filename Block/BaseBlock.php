<?php

namespace Wizzy\Search\Block;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;
use Wizzy\Search\Helpers\AddToWishlistHelper;
use Wizzy\Search\Helpers\UrlHelper;
use Wizzy\Search\Services\Request\CategoryManager;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Wizzy\Search\Services\Store\StoreCredentialsConfig;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Store\StoreSearchConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Wizzy\Search\Services\Store\StoreSearchFormConfig;

class BaseBlock extends Template
{

    private $storeAutocompleteConfig;
    private $storeSearchConfig;
    private $storeCredentialsConfig;
    private $storeManager;
    private $storeGeneralConfig;
    private $storeSearchFormConfig;
    private $categoryRequestManager;

    private $priceCurrency;
    private $searchDataHelper;
    private $formKey;
    private $urlHelper;

    private $addToWishlistHelper;

    public function __construct(
        Template\Context $context,
        CategoryManager $categoryRequestManager,
        StoreSearchFormConfig $storeSearchFormConfig,
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        StoreCredentialsConfig $storeCredentialsConfig,
        StoreSearchConfig $storeSearchConfig,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Search\Helper\Data $searchDataHelper,
        FormKey $formKey,
        UrlHelper $urlHelper,
        AddToWishlistHelper $addToWishlistHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->storeSearchConfig = $storeSearchConfig;
        $this->storeCredentialsConfig = $storeCredentialsConfig;
        $this->storeManager = $storeManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->storeSearchFormConfig = $storeSearchFormConfig;

        $this->storeAutocompleteConfig->setStore($this->storeManager->getCurrentStoreId());
        $this->storeSearchConfig->setStore($this->storeManager->getCurrentStoreId());
        $this->storeCredentialsConfig->setStore($this->storeManager->getCurrentStoreId());

        $this->priceCurrency = $priceCurrency;
        $this->categoryRequestManager = $categoryRequestManager;
        $this->searchDataHelper = $searchDataHelper;
        $this->formKey = $formKey;
        $this->urlHelper = $urlHelper;

        $this->addToWishlistHelper = $addToWishlistHelper;
    }

    private function getAddToCartParams()
    {
        return [
           'formAction' => $this->urlHelper->getAddToCartAction(
               $this->_urlBuilder,
               $this->_urlBuilder->getCurrentUrl()
           ),
           'formKey'    => $this->formKey->getFormKey(),
           'display'     => $this->storeSearchConfig->hasToDisplayAddToCartButton(),
        ];
    }

    public function getConfigs()
    {
        $category = $this->categoryRequestManager->getCategory();
        $isCategoryPage = $this->categoryRequestManager->isCategoryReplaceable();
        $categoryKey = ($isCategoryPage) ? $category->getUrlKey() : '';

        $hasToReplaceCategoryPage = $this->storeGeneralConfig->hasToReplaceCategoryPage();

        $configs = [
         'credentials' => [
            'apiKey' => $this->storeCredentialsConfig->getApiKey(),
            'storeId' => $this->storeCredentialsConfig->getStoreId(),
         ],
         'search' => [
            'addToCart' => $this->getAddToCartParams(),
            'addToWishlist' => $this->addToWishlistHelper->getAddParams($this->_urlBuilder),
            'enabled' => $this->storeGeneralConfig->isInstantSearchEnabled(),
            'input' => [
               'placeholder' => __($this->storeSearchFormConfig->getSearchInputPlaceholder()),
            ],
            'configs' => [
               'general' => [
                  'dom' => $this->storeSearchConfig->getDOMSelector(),
                  'searchEndpoint' => $this->storeSearchConfig->getSearchEndpoint(),
                  'noOfProducts' => $this->storeSearchConfig->getNoOfProducts(),
                  'includeOutOfStock' => $this->storeSearchConfig->hasToIncludeOutOfStockProducts(),
                  'behaviour' => $this->storeGeneralConfig->getInstantSearchBehaviour(),
               ],
               'facets' => [
                  'configs' => $this->storeSearchConfig->getFacetsConfiguration(),
                  'categoryDisplay' => $this->storeSearchConfig->getCategoryDisplayMethod(),
               ],
               'sorts' => [
                  'configs' => $this->storeSearchConfig->getSortConfiguration(),
               ],
               'swatches' => [
                  'configs' => $this->storeSearchConfig->getSwatchesConfiguration(),
               ],
               'pagination' => [
                  'type' => $this->storeSearchConfig->getPaginationType(),
                  'moveToTopWidget' => [
                     'add' => $this->storeSearchConfig->hasToAddMoveToTopWidget(),
                  ],
               ],
            ],
            'view' => [
               'domSelector' => '.columns',
               'templates' => [
                  'summary' => '#wizzy-search-summary',
                  'wrapper' => '#wizzy-search-wrapper',
                  'results' => '#wizzy-search-results',
                  'product' => '#wizzy-search-results-product',
                  'emptyResults' => '#wizzy-search-empty-results',
                  'facets'   => [
                     'common' => '#wizzy-facet-block',
                     'item' => '#wizzy-facet-item-common',
                     'rangeItem' => '#wizzy-facet-range-item',
                     'commonRangeAboveItem' => '#wizzy-facet-range-above-item',
                     'categoryItem' => '#wizzy-facet-category-item',
                     'selectedItem' => '#wizzy-selected-facet-item-common',
                     'selectedCommon' => '#wizzy-selected-facets-block',
                  ],
                  'pagination' => '#wizzy-search-pagination',
                  'sort' => '#wizzy-search-sort',
               ],
            ]
         ],
         'common' => [
            'isOnCategoryPage' => ($hasToReplaceCategoryPage && $isCategoryPage),
            'categoryUrlKey' => $categoryKey,
            'view' => [
               'templates' =>[
                  'progress' => '#wizzy-progress',
                  'select' => '#wizzy-common-select',
               ],
            ],
         ],
         'autocomplete' => [
            'enabled' => $this->storeGeneralConfig->isAutocompleteEnabled(),
            'menu' => [
               'suggestionsCount' => $this->storeAutocompleteConfig->getSuggestionsCount(),
               'alignment' => $this->storeAutocompleteConfig->getMenuAlignment(),
               'noResultsBehaviour' => $this->storeAutocompleteConfig->getNoResultsBehaviour(),
               'noResultsText' => __($this->storeAutocompleteConfig->getNoResultsText()),
               'categories' => [
                  'title' => __($this->storeAutocompleteConfig->getCategoriesTitle()),
               ],
               'others' => [
                  'title' => __($this->storeAutocompleteConfig->getOthersTitle()),
               ],
               'view' => [
                  'menu' => '.wizzy-autocomplete-wrapper',
                  'selectable' => 'selectable',
                  'searchterm' => 'searchterm',
                  'position' => $this->storeAutocompleteConfig->getMenuAlignment(),
                  'text-wrapper' => '.autocomplete-text-wrapper',
                  'wrapper' => '.wizzy-body-end-wrapper',
                  'topProductsBlock' => '.wizzy-autocomplete-top-products',
                  'suggestionLink' => '.autocomplete-link',
                  'templates' => [
                     'wrapper' => '#wizzy-autocomplete-wrapper',
                     'suggestions' => '#wizzy-autocomplete-suggestions',
                     'products' => '#wizzy-autocomplete-topproducts'
                  ]
               ],
            ],
            'topProducts' => [
               'suggestTopProduts' => $this->storeAutocompleteConfig->hasToShowTopProducts(),
               'count' => $this->storeAutocompleteConfig->getTopProductsCount(),
               'title' => __($this->storeAutocompleteConfig->getTopProductsTitle()),
            ],
            'pages' => [
               'title' => __($this->storeAutocompleteConfig->getPagesTitle()),
            ],
         ],
         'pageStore' => [

         ],
         'store' => [
            'currency' => [
               'code' => $this->storeManager->getCurrentStoreCurrency()->getCode(),
               'symbol' => $this->priceCurrency->getCurrencySymbol(),
            ],
         ],
        ];

        return $configs;
    }

    public function getSearchDataHelper()
    {
        return $this->searchDataHelper;
    }

    public function getReusableHTML($template)
    {
        return $this
          ->getLayout()
          ->createBlock(\Magento\Framework\View\Element\Template::class)
          ->setTemplate($template)
          ->toHtml();
    }
}
