define(['wizzy/libs/pageStore', 'jquery'], function(pageStore, $) {
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

    function getScrollOffset() {
        var offset = 600;
        if ($(window).width() <= 768) {
            offset = 700;
        }

        if (typeof wizzyConfig.search.configs.pagination.infiniteScrollOffset !== "undefined") {
            if (typeof wizzyConfig.search.configs.pagination.infiniteScrollOffset.desktop !== "undefined") {
                offset = wizzyConfig.search.configs.pagination.infiniteScrollOffset.desktop;
            }

            if ($(window).width() <= 768 && typeof wizzyConfig.search.configs.pagination.infiniteScrollOffset.mobile !== "undefined") {
                offset = wizzyConfig.search.configs.pagination.infiniteScrollOffset.mobile;
            }
        }

        return offset;
    }

    return {
        isInfiniteScroll: isInfiniteScroll,
        getTotalPages: getTotalPages,
        getCurrentPage: getCurrentPage,
        getScrollOffset: getScrollOffset,
    };

});
