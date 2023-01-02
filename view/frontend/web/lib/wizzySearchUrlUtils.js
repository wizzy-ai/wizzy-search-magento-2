define(['jquery', 'wizzy/utils', 'wizzy/listeners/urlChange', 'wizzy/utils/url', 'wizzy/utils/filters', 'wizzy/renderers/search', 'wizzy/utils/search'], function($, wizzyUtils, urlChangeListener, commonUrlUtils, filterUtils, searchRenderer, searchUtils) {

    var filtersUtilsVar = filterUtils.new();

    function updateQuery(query) {
        var searchData = {
            q: query,
        };
        var title = getSearchTitle(searchData.q);
        pushWindowState(searchData, title, getQueryUrl(searchData));
    }

    function redirectToQuery(query) {
        var searchData = {
            q: query,
        };
        redirectWindow(getQueryUrl(searchData));
    }

    function updateFilters(filters) {
        var searchData = filters;
        var q = getQueryFromFilters(searchData, true);
        var title = document.title;
        if (typeof q !== "undefined") {
            title = getSearchTitle(q);
        }
        pushWindowState(searchData, title, getQueryUrl(searchData, false));
    }

    function getSearchTitle(q) {
        return 'Search Results for: ' + q;
    }

    function updatePage(url, title) {
        pushWindowState({
            'url': url,
        }, title, url);
    }

    function pushWindowState(data, title, url) {
        history.pushState(data, title, url);
        changeDocumentTitle(title);
    }

    function redirectWindow(url) {
        window.location.href = url;
    }

    function changeDocumentTitle(title) {
        document.title = title;
    }

    function listenChanges(searchElement) {
        window.onpopstate = function(e){
            if (e.state) {
                var searchParams = (typeof e.state !== "undefined") ? e.state : "";
                if ((typeof searchParams.q != "undefined" && searchParams.q.trim() != "") || (typeof searchParams.categories != "undefined" && searchParams.categories.length > 0)) {
                    setSearchInputValue(searchElement);
                }
                else {
                    emptySearchElement(searchElement);
                }
            }
            else if(isOnCategoryPage()) {
                searchCategory(window.wizzyConfig.common.categoryUrlKey);
            }
            else if(isOnSearchPage()) {
                setSearchInputValue(searchElement);
            }
            else {
                emptySearchElement(searchElement);
            }
            urlChangeListener.onChange(e);
        };

        $(document).ready(function(e) {
            if (isOnSearchPage()) {
                setActivityIndicator();
                setSearchInputValue(searchElement);
            }
            if (isOnCategoryPage()) {
                searchCategory(window.wizzyConfig.common.categoryUrlKey);
            }
        });
    }

    function setActivityIndicator() {
        searchRenderer.showIndicatorOnPageLoad();
    }

    function isOnCategoryPage() {
        return window.wizzyConfig.common.isOnCategoryPage && window.wizzyConfig.common.categoryUrlKey !== "";
    }

    function emptySearchElement(searchElement) {
        searchElement.val('');
    }

    function isOnSearchPage() {
        var urlResponse = wizzyUtils.qS().parseUrl(window.location.href);
        return commonUrlUtils.hasSearchEndPointInUrl(urlResponse.url, false);
    }

    function searchCategory(categoryKey) {
        filtersUtilsVar.decodeFilters();
        $.fn.categorySearch(categoryKey);
    }

    function setSearchInputValue(searchElement) {
        var decodedFilters = filterUtils.new().decodeFilters();

        var searchTitle  = "";
        var searchedQuery = "";

        if (isValidQueryString(decodedFilters.q)) {
            searchedQuery = decodedFilters.q;
            searchTitle = getSearchTitle(searchedQuery);
        }
        else {
            searchedQuery = getQueryFromFilters(decodedFilters, false);
            searchTitle = getSearchTitle(searchedQuery);
        }

        $.fn.setSearchedTitle(searchedQuery);
        changeDocumentTitle(searchTitle);
        executeSearchByElement(searchElement, decodedFilters.q);
    }

    function getQueryFromFilters(filters, onChange) {
        if (onChange && !isOnCategoryPage()) {
            return filters.q;
        }
        if (typeof filters['fq'] !== "undefined" && isValidQueryString(filters['fq'])) {
            return filters['fq'];
        }
        if (typeof filters['categories'] !== "undefined" && filters['categories'].length > 0) {
            return filters['categories'][0];
        }
        return '';
    }

    function isValidQueryString(q) {
        return (typeof q !== "undefined" && (q.trim().length >= searchUtils.getMinQueryLength() || q.trim().length === 0));
    }

    function hasToExecuteSearch() {
        let originUrl = window.location.href;
        let params = originUrl.split("?");
        if (params.length <= 1) {
            return false;
        }
        params = params[1];
        params = params.split("&");
        if (params.length > 1) {
            return false;
        }
        return true;
    }

    function executeSearchByElement(searchElement, q) {
        if (isValidQueryString(q)) {
            searchElement.val(q);
        }
        if(hasToExecuteSearch()) {
            $.fn.executeSearch({
                "q": q,
                "sF": true
            });
        } else {
            $.fn.refreshFilters(true);
        }
    }

    function getParam(param) {
        var urlResponse = wizzyUtils.qS().parseUrl(window.location.href);
        return (typeof urlResponse.query[param] !== "undefined") ? urlResponse.query[param] : '';
    }

    function getQueryUrl(searchData, isBySearch = true) {
        if(isOnCategoryPage() && isBySearch === false) {
            return getOrigin() + "/" + commonUrlUtils.getCategoriesEndPoint() + "?" + filtersUtilsVar.encodeFilters(searchData);
        }
        return getOrigin() + commonUrlUtils.getSearchEndPoint() + "?" + filterUtils.new().encodeFilters(searchData);
    }

    function getOrigin() {
        return window.location.origin;
    }

    function hasSearchEndPointInUrl(url) {
        commonUrlUtils.hasSearchEndPointInUrl(url);
    }

    return {
        updateQuery: updateQuery,
        redirectToQuery: redirectToQuery,
        updatePage: updatePage,
        updateFilters: updateFilters,
        hasSearchEndPointInUrl: hasSearchEndPointInUrl,
        listenChanges: listenChanges,
        isOnSearchPage: isOnSearchPage
    };

});
