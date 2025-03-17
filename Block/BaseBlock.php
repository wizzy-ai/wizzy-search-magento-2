<?php

namespace Wizzy\Search\Block;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\Template;
use Wizzy\Search\Helpers\AddToWishlistHelper;
use Wizzy\Search\Helpers\UrlHelper;
use Wizzy\Search\Model\Admin\Source\CategoryClickBehaviours;
use Wizzy\Search\Model\Admin\Source\LeftFacetsDefaultBehaviourSelection;
use Wizzy\Search\Services\Request\CategoryManager;
use Wizzy\Search\Services\Request\ProductManager;
use Wizzy\Search\Services\Store\StoreAdvancedConfig;
use Wizzy\Search\Services\Store\StoreAutocompleteConfig;
use Wizzy\Search\Services\Store\StoreCredentialsConfig;
use Wizzy\Search\Services\Store\StoreGeneralConfig;
use Wizzy\Search\Services\Store\StoreManager;
use Wizzy\Search\Services\Store\StoreSearchConfig;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Wizzy\Search\Services\Store\StoreSearchFormConfig;
use Wizzy\Search\Services\Store\StoreStockConfig;
use Wizzy\Search\Block\Adminhtml\Version;

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
    private $storeStockConfig;
    private $productManager;
    private $storeAdvancedConfig;
    private $version;

    public function __construct(
        Template\Context $context,
        CategoryManager $categoryRequestManager,
        StoreSearchFormConfig $storeSearchFormConfig,
        StoreManager $storeManager,
        StoreGeneralConfig $storeGeneralConfig,
        StoreCredentialsConfig $storeCredentialsConfig,
        StoreSearchConfig $storeSearchConfig,
        StoreStockConfig $storeStockConfig,
        StoreAutocompleteConfig $storeAutocompleteConfig,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Search\Helper\Data $searchDataHelper,
        FormKey $formKey,
        UrlHelper $urlHelper,
        AddToWishlistHelper $addToWishlistHelper,
        StoreAdvancedConfig $storeAdvancedConfig,
        ProductManager $productManager,
        Version $version,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->storeAutocompleteConfig = $storeAutocompleteConfig;
        $this->storeSearchConfig = $storeSearchConfig;
        $this->storeCredentialsConfig = $storeCredentialsConfig;
        $this->storeManager = $storeManager;
        $this->storeGeneralConfig = $storeGeneralConfig;
        $this->storeSearchFormConfig = $storeSearchFormConfig;
        $this->storeStockConfig = $storeStockConfig;
        $this->storeAdvancedConfig = $storeAdvancedConfig;

        $currentStoreId = $this->storeManager->getCurrentStoreId();

        $this->storeAutocompleteConfig->setStore($currentStoreId);
        $this->storeSearchConfig->setStore($currentStoreId);
        $this->storeStockConfig->setStore($currentStoreId);
        $this->storeCredentialsConfig->setStore($currentStoreId);
        $this->storeAdvancedConfig->setStore($currentStoreId);

        $this->priceCurrency = $priceCurrency;
        $this->categoryRequestManager = $categoryRequestManager;
        $this->searchDataHelper = $searchDataHelper;
        $this->formKey = $formKey;
        $this->urlHelper = $urlHelper;

        $this->addToWishlistHelper = $addToWishlistHelper;
        $this->productManager = $productManager;
        $this->version = $version;
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

    private function getAddToWishlistParams()
    {
        return [
          'postData' => $this->addToWishlistHelper->getAddParams($this->_urlBuilder),
          'display'  => $this->storeSearchConfig->hasToDisplayAddToWishlistButton(),
        ];
    }

    public function getConfigs()
    {
        $category = $this->categoryRequestManager->getCategory();
        $isCategoryPage = $this->categoryRequestManager->isCategoryReplaceable();
        $categoryKey = ($isCategoryPage) ? $category->getUrlKey() : '';
        $categoryEndPoint = ($isCategoryPage) ? $this->categoryRequestManager->getCategoryEndpoint() : '';

        $hasToReplaceCategoryPage = $this->storeGeneralConfig->hasToReplaceCategoryPage();

        $currencyCode = $this->storeManager->getCurrentStoreCurrency()->getCode();
        $currencySymbol = $this->priceCurrency->getCurrencySymbol();

        $configs = [
         'version' => $this->version->getExtensionVersion(),
         'credentials' => [
            'apiKey' => $this->storeCredentialsConfig->getApiKey(),
            'storeId' => $this->storeCredentialsConfig->getStoreId(),
         ],
         'search' => [
            'addToCart' => $this->getAddToCartParams(),
            'addToWishlist' => $this->getAddToWishlistParams(),
            'enabled' => $this->storeGeneralConfig->isInstantSearchEnabled(),
            'input' => [
               'placeholder' => __($this->storeSearchFormConfig->getSearchInputPlaceholder()),
            ],
            'tileView' => [
               'display' => $this->storeSearchConfig->hasToDisplayTileView(),
            ],
            'configs' => [
               'general' => [
                  'dom' => $this->storeSearchConfig->getDOMSelector(),
                  'searchEndpoint' => $this->storeSearchConfig->getSearchEndpoint(),
                  'noOfProducts' => $this->storeSearchConfig->getNoOfProducts(),
                  'includeOutOfStock' => $this->storeStockConfig->hasToIncludeOutOfStockProducts(),
                  'behaviour' => $this->storeGeneralConfig->getInstantSearchBehaviour(),
                  'formSubmissionBehaviour' => $this->storeGeneralConfig->getFormSubmissionBehaviour(),
               ],
               'facets' => [
                  'configs' => $this->storeSearchConfig->getFacetsConfiguration(),
                  'categoryDisplay' => $this->storeSearchConfig->getCategoryDisplayMethod(),
                  'leftFacets' => [
                      'collapsible' => $this->storeSearchConfig->leftFacetsHasToBeCollapsible(),
                      'defaultCollapsed' =>
                          ($this->storeSearchConfig->leftFacetsCollapsibleBehaviour() ===
                              LeftFacetsDefaultBehaviourSelection::COLLAPSED),
                      'firstLeftDefaultOpened' =>
                          ($this->storeSearchConfig->leftFirstFacetCollapsibleBehaviour() ===
                              LeftFacetsDefaultBehaviourSelection::OPENED),
                  ],
               ],
               'sorts' => [
                  'configs' => $this->storeSearchConfig->getSortConfiguration(),
               ],
               'swatches' => [
                  'configs' => $this->storeSearchConfig->getSwatchesConfiguration(),
               ],
               'pagination' => [
                  'type' => $this->storeSearchConfig->getPaginationType(),
                  'infiniteScrollOffset' => [
                     'desktop' => $this->storeSearchConfig->getDesktopScrollOffset(),
                     'mobile' => $this->storeSearchConfig->getMobileScrollOffset(),
                  ],
                  'moveToTopWidget' => [
                     'add' => $this->storeSearchConfig->hasToAddMoveToTopWidget(),
                  ],
               ],
            ],
            'view' => [
               'domSelector' => $this->storeSearchConfig->getDOMSelector(),
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
            'isOnCategoryListing' => ($isCategoryPage),
            'isOnProductViewPage' => $this->productManager->isOnProductPage(),
            'categoryUrlKey' => $categoryKey,
            'categoryEndpoint' => $categoryEndPoint,
            'currentProductId' => $this->productManager->getProductId(),
            'view' => [
               'templates' =>[
                  'progress' => '#wizzy-progress',
                  'select' => '#wizzy-common-select',
                  'literals' => [
                     'sortBy' => __('Sort By'),
                  ]
               ],
            ],
            'templateAttributes' => $this->storeAdvancedConfig->getTemplateAttributes(),
         ],
         'autocomplete' => [
            'enabled' => $this->storeGeneralConfig->isAutocompleteEnabled(),
            'configs' => [
               'general' => [
                  'openCategoryPage' =>
                     (
                        $this->storeGeneralConfig->getCategoryClickBehaviour() ===
                        CategoryClickBehaviours::OPEN_CATEGORY_PAGE
                     )
               ],
               'defaultBehaviour' => [
                  'suggestions' => [
                     'defaultPool' => $this->storeAutocompleteConfig->getPinnedTermSelections(),
                     'displayRecent' => $this->storeAutocompleteConfig->hasToDisplayRecentlySearchedTerms(),
                     'enabled' => $this->storeAutocompleteConfig->hasToShowDefaultSuggestions(),
                  ],
                  'topProducts' => [
                     "defaultPool" => [
                        "method" => "filters",
                        "expiry" => "session",
                        "data" => [
                           "categories" => $this->storeAutocompleteConfig->getPinnedProductsSelections(),
                           "productsCount" => $this->storeAutocompleteConfig->getPinnedProductsCount(),
                        ]
                     ],
                     'displayRecent' => $this->storeAutocompleteConfig->hasToDisplayRecentlyViewedProducts(),
                     'enabled' => $this->storeAutocompleteConfig->hasToShowDefaultProducts(),
                  ],
               ],
            ],
            'menu' => [
               'suggestionsCount' => $this->storeAutocompleteConfig->getSuggestionsCount(),
               'alignment' => $this->storeAutocompleteConfig->getMenuAlignment(),
               'noResultsBehaviour' => $this->storeAutocompleteConfig->getNoResultsBehaviour(),
               'noResultsText' => __($this->storeAutocompleteConfig->getNoResultsText()),
               'sections' => $this->storeAutocompleteConfig->getSectionsConfiguration(),
               'categories' => [
                  'title' => __($this->storeAutocompleteConfig->getCategoriesTitle()),
               ],
               'others' => [
                  'title' => __($this->storeAutocompleteConfig->getOthersTitle()),
               ],
               'brands' => [
                   'title' => __($this->storeAutocompleteConfig->getBrandsTitle()),
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
            'recentSearches' => [
               'title' => "Recent Searches",
            ],
            'searchBar' => [
               'hasAnimatedPlaceholders' => $this->storeSearchFormConfig->hasToEnableAnimatedPlaceholders(),
               'animatedPlaceholders' => $this->storeSearchFormConfig->getAnimatedPlaceholderTerms(),
            ],
         ],
         'pageStore' => [

         ],
         'store' => [
            'currency' => [
               'code' => $currencyCode,
               'symbol' => ($currencySymbol) ? $currencySymbol : ($currencyCode . " "),
            ],
         ],
         'analytics' => [
            'enabled' => $this->storeGeneralConfig->isAnalyticsEnabled(),
            'endpoints' => [
               'clicks' => "/wtrack/analytics/collect",
               'sessions' => '/wtrack/analytics/session',
            ],
         ],
         'filters' => [
            'configs' => [
               'displayAsDrawer' => $this->storeSearchConfig->leftFacetsHasToDisplayAsDrawer(),
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
