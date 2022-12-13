define(['jquery'], function($) {

    $(document).ready(function(e) {
        setDefaultStore();
    });

    function setDefaultStore() {
        window.wizzyConfig.pageStore = {
            'suggestionFilters': [],
            'searchInputValue': '',
            'filteringFor' : '',
        };
    }

    function set(key, value) {
        if (typeof window.wizzyConfig.pageStore[key] === "undefined") {
            window.wizzyConfig.pageStore[key] = "";
        }
        window.wizzyConfig.pageStore[key] = value;
    }

    function get(key, defaultValue = "") {
        if (typeof window.wizzyConfig.pageStore[key] !== "undefined") {
            return window.wizzyConfig.pageStore[key];
        }

        return defaultValue;
    }

    return {
        set: set,
        get: get,
        keys: {
            suggestionFilters: 'suggestionFilters',
            searchInputValue: 'searchInputValue',
            searchSubmitValue: 'searchSubmitValue',
            beforeSearchDOM: 'beforeSearchDOM',
            searchedResponse: 'searchedResponse',
            lastRequestIdSearch: 'lastRequestIdSearch',
            lastRequestIdFilters: 'lastRequestIdFilters',
            lastGroupRequestIdFilters: 'lastGroupRequestIdFilters',
            filterRequests: 'filterRequests',
            lastRequestIdAutocomplete: 'lastRequestIdAutocomplete',
            selectedSortMethod: 'selectedSortMethod',
            selectedFilters: 'selectedFilters',
            isPaginating: 'isPaginating',
            isCategoryPageRendered: 'isCategoryPageRendered',
            hasMoreResults: 'hasMoreResults',
            isNumberPaginating: 'isNumberPaginating',
            lastResponseId: 'lastResponseId',
            lastExecutedFilters: 'lastExecutedFilters',
            groupedFilteredProducts: 'groupedFilteredProducts',
        }
    };

});