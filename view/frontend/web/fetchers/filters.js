define(['jquery', 'wizzy/common', 'wizzy/libs/pageStore', 'wizzy/utils/filters', 'wizzy/libs/searchUrlUtils', 'wizzy/utils/search', 'wizzy/renderers/search', 'wizzy/utils/pagination'], function($, wizzyCommon, pageStore, filtersUtils, urlUtils, searchUtils, searchRenderer, paginationUtils) {
    function execute(options) {
        var filters = typeof options['filters'] === "undefined" ? {} : options['filters'];
        var filteringFor = typeof options['for'] === "undefined" ? '' : options['for'];
        var isCategorySearch = typeof options['isCategorySearch'] === "undefined" ? false: options['isCategorySearch'];

        if (filteringFor === "") {
            return;
        }
        if (filteringFor === "menu") {
            if (!window.wizzyConfig.autocomplete.topProducts.suggestTopProduts || window.wizzyConfig.autocomplete.topProducts.count <= 0) {
                return;
            }
            filters['productsCount'] = window.wizzyConfig.autocomplete.topProducts.count;
        }

        filters = setDefaultValuesInFilters(filters);

        var payload = {
            filters: filters,
        };

        executeFilter(payload, isCategorySearch, filteringFor);
    }

    function setDefaultValuesInFilters(filters) {
        if (typeof filters.facets === "undefined") {
            filters.facets = searchUtils.getFacetsToAdd();
        }
        if (typeof filters.inStock === "undefined" && window.wizzyConfig.search.configs.general.includeOutOfStock) {
            filters.inStock = [];
        }
        if (typeof filters.swatch === "undefined") {
            filters.swatch = searchUtils.getSwatchesToAdd();
        }
        if (typeof filters.currency === "undefined" || !filters.currency) {
            filters.currency = window.wizzyConfig.store.currency.code;
        }
        if (typeof filters.productsCount === "undefined" || !filters.productsCount) {
            filters.productsCount = window.wizzyConfig.search.configs.general.noOfProducts;
        }
        if (typeof filters.sort === "undefined" || !filters.sort) {
            var sort = pageStore.get(pageStore.keys.selectedSortMethod, null);
            if (sort) {
                filters['sort'] = [
                    {
                        field: sort['field'],
                        order: sort['order'],
                    }
                ];
            }
        }

        return filters;
    }

    function executeFilter(payload, isCategorySearch, filteringFor) {
        var hasWizzyClient = true;
         if (typeof wizzyCommon.getClient() === "undefined") {
            hasWizzyClient = false;
            var clientCheckInterval = setInterval(clientCheckTimer, 100);
            var clickCheckInstances = 0;
    
            function clientCheckTimer() {
                if (typeof wizzyCommon.getClient() !== "undefined") {
                    hasWizzyClient = true;
                    executeFilterAct(payload, isCategorySearch, filteringFor);
                }
                clickCheckInstances++;
                if (clickCheckInstances || hasWizzyClient) {
                    clearInterval(clientCheckInterval);
                }
            }
        }
        else {
            executeFilterAct(payload, isCategorySearch, filteringFor);
        }
    }
    
    function executeFilterAct(payload, isCategorySearch, filteringFor) {
        if (filteringFor === 'page') {
            searchRenderer.showIndicator(true, !isCategorySearch);
        }

        payload['group'] = filteringFor;
        payload = wizzy.triggerEvent(wizzy.allowedEvents.BEFORE_FILTERS_EXECUTED, payload);
        var response = wizzyCommon.getClient().filter(payload);
        var lastRequestIdFilters = pageStore.get(pageStore.keys.lastRequestIdFilters, {});
        lastRequestIdFilters[filteringFor] = response.requestId;
        pageStore.set(pageStore.keys.lastRequestIdFilters, lastRequestIdFilters);

        var filterRequests = pageStore.get(pageStore.keys.filterRequests, {});
        filterRequests[response.requestId] = filteringFor;

        pageStore.set(pageStore.keys.filterRequests, filterRequests);
    }

    function setSortInFilters() {
        var sortMethod = pageStore.get(pageStore.keys.selectedSortMethod, null);
        if (sortMethod !== null) {
            filtersUtils.new().setSort(sortMethod['field'], sortMethod['order']);
        }
    }

    function applySort() {
        window.scrollTo(0, 0);
        setSortInFilters();
        resetPage();
        refreshFilters(false, false, false);
    }

    function categorySearch(categoryKey) {
        filtersUtils.new().clearAll();
        clear('q');
        pageStore.set(pageStore.keys.searchInputValue, null);
        resetPage();
        filtersUtils.new().addCategoryFilter(categoryKey);
        refreshFilters(true, false, true);
    }

    function resetPage() {
        filtersUtils.new().setPage(1);
    }

    function setPaginating() {
        if (paginationUtils.isInfiniteScroll()) {
            pageStore.set(pageStore.keys.isPaginating, true);
        }
        else {
            if ($(window).width() > 768) {
                window.scrollTo(0, 0);
            }
            pageStore.set(pageStore.keys.isNumberPaginating, true);
        }
    }

    function applyNextPage() {
        setPaginating();
        var searchedResponse = pageStore.get(pageStore.keys.searchedResponse, null);
        if (searchedResponse !== null) {
            var currentPage = paginationUtils.getCurrentPage();
            var nextPage = parseInt(currentPage) + 1;

            if (typeof searchedResponse['pages'] !== "undefined" && nextPage <= searchedResponse['pages']) {
                filtersUtils.new().setPage(nextPage);
                refreshFilters(false, true, false);
            }
        }
    }

    function applyPrevPage() {
        setPaginating();
        var searchedResponse = pageStore.get(pageStore.keys.searchedResponse, null);
        if (searchedResponse !== null) {
            var currentPage = paginationUtils.getCurrentPage();
            var prevPage = parseInt(currentPage) - 1;

            if (typeof searchedResponse['pages'] !== "undefined" && prevPage >= 1) {
                filtersUtils.new().setPage(prevPage);
                refreshFilters(false, true, false);
            }
        }
    }

    function jumpToPage(page) {
        setPaginating();
        window.scrollTo(0, 0);
        page = parseInt(page);
        filtersUtils.new().setPage(page);
        refreshFilters(false, true, false);
    }

    function apply(facetKey, filterKey, isFromSelected) {
        window.scrollTo(0, 0);
        resetPage();
        if (isCategoryHierarchyFilter(facetKey) && !isFromSelected) {
            filtersUtils.new().clearFilters(facetKey);
            filterKey = getUpdateCategoryFilterKey(facetKey, filterKey);
            if (filterKey !== null) {
                filtersUtils.new().addOrRemoveFilter(facetKey, filterKey);
            }
        }
        else {
            filtersUtils.new().addOrRemoveFilter(facetKey, filterKey);
        }
        refreshFilters(false, false, false);
    }

    function getUpdateCategoryFilterKey(facetKey, filterKey) {
        var element = $('.facet-body-' + facetKey).find('.wizzy-facet-list-item[data-key="'+filterKey+'"]');
        if(typeof element !== "undefined" && element !== null && element.size() > 0 && !element.hasClass('active')) {
            var parents = element.parents('.wizzy-facet-list-item.active');
            if (parents.size() > 0) {
                filterKey = parents.first().data('key');
            }
            else {
                return null;
            }
        }

        return filterKey;
    }

    function isCategoryHierarchyFilter(facetKey) {
        return window.wizzyConfig.search.configs.facets.categoryDisplay === "hierarchy" && facetKey === "categories";
    }

    function clear(key) {
        filtersUtils.new().clearFilters(key);
    }

    function clearAll() {
        filtersUtils.new().clearAll();
        refreshFilters(false, false, false);
    }

    function setDefaultsFromFilters(filters) {
        if (typeof filters['sort'] !== "undefined" && filters['sort'].length > 0) {
            pageStore.set(pageStore.keys.selectedSortMethod, filters['sort'][0]);
        }
        if (paginationUtils.isInfiniteScroll()) {
            filters['page'] = 1;
        }
    }

    function isSameAsPreviousFilters(filters) {
        var lastExecutedFilters = pageStore.get(pageStore.keys.lastExecutedFilters, null);
        var currentFilters = JSON.stringify(filters);
        if (lastExecutedFilters != currentFilters) {
            pageStore.set(pageStore.keys.lastExecutedFilters, currentFilters)
            return false;
        }
    
        return true;
    }

    function refreshFilters(isFromPageLoad, isByPagination, isCategorySearch) {
        var filters = filtersUtils.new().getFilters();
        if (typeof filters['sort'] === "undefined") {
            setSortInFilters();
        }
        if (isFromPageLoad) {
            setDefaultsFromFilters(filters);
        }
        if (isSameAsPreviousFilters(filters)) {
            return;
        }
        execute({
            'filters': filters,
            'for': 'page',
            'isByPagination': isByPagination,
            'isCategorySearch': isCategorySearch,
        });
        if (!isFromPageLoad && ((isByPagination && !paginationUtils.isInfiniteScroll() && $(window).width() > 768) || !isByPagination)) {
            urlUtils.updateFilters(filters);
        }
    }

    return {
        execute: execute,
        refreshFilters: refreshFilters,
        apply: apply,
        applySort: applySort,
        clearAll: clearAll,
        categorySearch: categorySearch,
        clear: clear,
        applyNextPage: applyNextPage,
        applyPrevPage: applyPrevPage,
        jumpToPage: jumpToPage,
    };
});
