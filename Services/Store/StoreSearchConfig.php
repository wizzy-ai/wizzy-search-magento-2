<?php

namespace Wizzy\Search\Services\Store;

class StoreSearchConfig
{
    private $configManager;

    const WIZZY_SEARCH_CONFIGURATION = "wizzy_search_configuration";

    const WIZZY_SEARCH_RESULTS_CONFIGURATION =
       self::WIZZY_SEARCH_CONFIGURATION . "/search_results_general_configuration";
    const WIZZY_DOM_SELECTOR = self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/dom_selector";
    const WIZZY_SEARCH_ENDPOINT = self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/search_endpoint";
    const WIZZY_NO_OF_PRODUCTS = self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/no_of_products";
    const WIZZY_DISPLAY_ADD_TO_CART_BUTTON =
       self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/display_add_to_cart_button";
    const WIZZY_DISPLAY_ADD_TO_WISHLIST_BUTTON =
       self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/display_add_to_wishlist_button";
    const WIZZY_DISPLAY_TILE_VIEW = self::WIZZY_SEARCH_RESULTS_CONFIGURATION . "/display_tile_view";

    const WIZZY_SEARCH_FACETS_CONFIGURATION = self::WIZZY_SEARCH_CONFIGURATION . "/search_results_facets_configuration";
    const WIZZY_FACETS = self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/facets_configuration";
    const WIZZY_FACETS_DISPLAY_AS_DRAWER =
        self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/left_facets_has_to_display_in_drawer";
    const WIZZY_FACET_CATEGORY_DISPLAY = self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/category_facet_display_method";
    const WIZZY_LEFT_FACETS_COLLAPSIBLE = self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/left_facets_has_to_collapsible";
    const WIZZY_LEFT_FACETS_DEFAULT_COLLAPSIBLE_BEHAVIOUR =
        self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/left_facets_default_collapsible_behaviour";
    const WIZZY_LEFT_FIRST_FACET_DEFAULT_COLLAPSIBLE_BEHAVIOUR =
        self::WIZZY_SEARCH_FACETS_CONFIGURATION . "/left_first_facet_default_collapsible_behaviour";

    const WIZZY_SEARCH_SORT_CONFIGURATION = self::WIZZY_SEARCH_CONFIGURATION . "/search_results_sorts_configuration";
    const WIZZY_SORT_CONFIGURATION =
       self::WIZZY_SEARCH_SORT_CONFIGURATION . "/sorts_configuration";
    const WIZZY_FILTERS_AND_SORT_ICON_POSITION = self::WIZZY_SEARCH_FACETS_CONFIGURATION
        . "/filters_and_sort_icon_position";
    const WIZZY_FILTERS_AND_SORT_ICON_OFFSET = self::WIZZY_SEARCH_FACETS_CONFIGURATION
        . "/filters_and_sort_icon_offset";
    const WIZZY_PAGINATION_CONFIGURATION =
       self::WIZZY_SEARCH_CONFIGURATION . "/search_results_pagination_configuration";
    const WIZZY_PAGINATION_TYPE = self::WIZZY_PAGINATION_CONFIGURATION . "/pagination_type";
    const WIZZY_PAGINATION_MOVE_TO_TOP_WIDGET =
       self::WIZZY_PAGINATION_CONFIGURATION . "/pagination_move_to_top_widget";
    const INFINITE_SCROLL_OFFSET_DESKTOP =
       self::WIZZY_PAGINATION_CONFIGURATION . "/infinite_scroll_offset_desktop";
    const INFINITE_SCROLL_OFFSET_MOBILE =
       self::WIZZY_PAGINATION_CONFIGURATION . "/infinite_scroll_offset_mobile";
    const WIZZY_SEARCH_SWATCHES_CONFIGURATION =
       self::WIZZY_SEARCH_CONFIGURATION . "/search_results_swatches_configuration";
    const WIZZY_SWATCHES_CONFIGURATION = self::WIZZY_SEARCH_SWATCHES_CONFIGURATION . "/swatches_configuration";
    const WIZZY_NO_RESULTS_PAGE_CONFIGURATION =
       self::WIZZY_SEARCH_CONFIGURATION . "/no_results_page_configuration";
    const NO_RESULTS_PAGE_TITLE = self::WIZZY_NO_RESULTS_PAGE_CONFIGURATION . "/no_results_page_title";
    const SHOW_NO_RESULTS_PRODUCTS_SUGGESTIONS =
       self::WIZZY_NO_RESULTS_PAGE_CONFIGURATION . "/show_no_results_products_suggestions";
    const NO_RESULTS_PRODUCTS_SELECTION = self::WIZZY_NO_RESULTS_PAGE_CONFIGURATION . "/no_results_products_selection";
    const NO_RESULTS_PRODUCTS_COUNT = self::WIZZY_NO_RESULTS_PAGE_CONFIGURATION . "/no_results_products_count";
    const NO_RESULTS_PAGE_SUB_TITLE = self::WIZZY_NO_RESULTS_PAGE_CONFIGURATION . "/no_results_page_sub_title";

    private $storeId;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function setStore(string $storeId)
    {
        $this->storeId = $storeId;
    }

    public function getDOMSelector()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_DOM_SELECTOR, $this->storeId);
    }

    public function getSearchEndpoint()
    {
        $endPoint = $this->configManager->getStoreConfig(self::WIZZY_SEARCH_ENDPOINT, $this->storeId);
        if (!$endPoint) {
           // default endpoint.
            return "/search";
        }
        return $endPoint;
    }

    public function getNoOfProducts()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_NO_OF_PRODUCTS, $this->storeId);
    }

    public function hasToDisplayAddToCartButton()
    {
        return ($this->configManager->getStoreConfig(self::WIZZY_DISPLAY_ADD_TO_CART_BUTTON, $this->storeId) == 1);
    }

    public function hasToDisplayAddToWishlistButton()
    {
        return ($this->configManager->getStoreConfig(self::WIZZY_DISPLAY_ADD_TO_WISHLIST_BUTTON, $this->storeId) == 1);
    }

    public function hasToDisplayTileView()
    {
        return ($this->configManager->getStoreConfig(self::WIZZY_DISPLAY_TILE_VIEW, $this->storeId) == 1);
    }

    public function getFacetsConfiguration()
    {
        $facetsConfig = $this->configManager->getStoreConfig(self::WIZZY_FACETS, $this->storeId);
        if (!$facetsConfig) {
            return [];
        }

        return json_decode($facetsConfig, true);
    }
    public function leftFacetsHasToDisplayAsDrawer()
    {
        return (
            $this->configManager->getStoreConfig(
                self::WIZZY_FACETS_DISPLAY_AS_DRAWER,
                $this->storeId
            ) == '1' ? true : false
        );
    }

    public function getCategoryDisplayMethod()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_FACET_CATEGORY_DISPLAY, $this->storeId);
    }

    public function leftFacetsHasToBeCollapsible()
    {
        return ($this->configManager->getStoreConfig(self::WIZZY_LEFT_FACETS_COLLAPSIBLE, $this->storeId) == 1);
    }

    public function leftFacetsCollapsibleBehaviour()
    {
        return $this->configManager->getStoreConfig(
            self::WIZZY_LEFT_FACETS_DEFAULT_COLLAPSIBLE_BEHAVIOUR,
            $this->storeId
        );
    }

    public function leftFirstFacetCollapsibleBehaviour()
    {
        return $this->configManager->getStoreConfig(
            self::WIZZY_LEFT_FIRST_FACET_DEFAULT_COLLAPSIBLE_BEHAVIOUR,
            $this->storeId
        );
    }

    public function getSortConfiguration()
    {
        $sortConfigs = $this->configManager->getStoreConfig(self::WIZZY_SORT_CONFIGURATION, $this->storeId);
        if (!$sortConfigs) {
            return [];
        }

        return json_decode($sortConfigs, true);
    }
    public function getFiltersAndSortIconPosition(): string
    {
        $filtersAndSortIconPosition = $this->configManager->getStoreConfig(
            self::WIZZY_FILTERS_AND_SORT_ICON_POSITION,
            $this->storeId
        );
        return $filtersAndSortIconPosition ?: 'bottom';
    }
    public function getFiltersAndSortIconOffset(): int
    {
        $filtersAndSortIconOffset = $this->configManager->getStoreConfig(
            self::WIZZY_FILTERS_AND_SORT_ICON_OFFSET,
            $this->storeId
        );
        return $filtersAndSortIconOffset ?: 0;
    }

    public function getSwatchesConfiguration()
    {
        $swatchesConfiguration = $this->configManager->getStoreConfig(
            self::WIZZY_SWATCHES_CONFIGURATION,
            $this->storeId
        );
        if (!$swatchesConfiguration) {
            return [];
        }

        return json_decode($swatchesConfiguration, true);
    }

    public function getPaginationType()
    {
        return $this->configManager->getStoreConfig(self::WIZZY_PAGINATION_TYPE, $this->storeId);
    }
    
    public function getDesktopScrollOffset()
    {
        return $this->configManager->getStoreConfig(self::INFINITE_SCROLL_OFFSET_DESKTOP, $this->storeId);
    }

    public function getMobileScrollOffset()
    {
        return $this->configManager->getStoreConfig(self::INFINITE_SCROLL_OFFSET_MOBILE, $this->storeId);
    }

    public function hasToAddMoveToTopWidget()
    {
        return ($this->configManager->getStoreConfig(self::WIZZY_PAGINATION_MOVE_TO_TOP_WIDGET, $this->storeId) == 1);
    }
    public function getNoResultsPageTitle()
    {
        return ($this->configManager->getStoreConfig(self::NO_RESULTS_PAGE_TITLE, $this->storeId));
    }

    public function hasToEnableProductSuggestionsOnNoResultPage()
    {
        return ($this->configManager->getStoreConfig(self::SHOW_NO_RESULTS_PRODUCTS_SUGGESTIONS, $this->storeId) == 1);
    }

    public function getNoResultsProductsSelection()
    {
        return ($this->configManager->getStoreConfig(self::NO_RESULTS_PRODUCTS_SELECTION, $this->storeId));
    }

    public function getNoResultsProductsCount()
    {
        return ($this->configManager->getStoreConfig(self::NO_RESULTS_PRODUCTS_COUNT, $this->storeId));
    }
    public function getNoResultsPageSubTitle()
    {
        return ($this->configManager->getStoreConfig(self::NO_RESULTS_PAGE_SUB_TITLE, $this->storeId));
    }
}
