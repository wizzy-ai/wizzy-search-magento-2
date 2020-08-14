define(['jquery'], function($) {
    function hasSearchEndPointInUrl(url) {
        var searchEndpoint = getSearchEndPoint();
        return url.endsWith(searchEndpoint) || url.indexOf(searchEndpoint) >= 0;
    }

    function getSearchEndPoint() {
        return window.wizzyConfig.search.configs.general.searchEndpoint;
    }

    return {
        getSearchEndPoint: getSearchEndPoint,
        hasSearchEndPointInUrl: hasSearchEndPointInUrl,
    };

});