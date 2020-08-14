define(['wizzy/libs/pageStore'], function(pageStore) {
    function isInfiniteScroll() {
        return "infinite_scroll" === wizzyConfig.search.configs.pagination.type;
    }

    function getCurrentPage() {
        var searchedResponse = pageStore.get(pageStore.keys.searchedResponse, null);
        return (searchedResponse !== null && typeof searchedResponse['filters'] !== "undefined" && typeof searchedResponse['filters']['page'] !== "undefined") ? searchedResponse['filters']['page']: 1;
    }

    function getTotalPages() {
        var searchedResponse = pageStore.get(pageStore.keys.searchedResponse, null);
        return (searchedResponse !== null && typeof searchedResponse['pages'] !== "undefined") ? parseInt(searchedResponse['pages']) : 0;
    }

    return {
        isInfiniteScroll: isInfiniteScroll,
        getTotalPages: getTotalPages,
        getCurrentPage: getCurrentPage,
    };

});