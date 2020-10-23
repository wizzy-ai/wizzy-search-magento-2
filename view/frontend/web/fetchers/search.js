define(['jquery', 'wizzy/common', 'wizzy/libs/pageStore', 'wizzy/renderers/search', 'wizzy/utils/search'], function($, wizzyCommon, pageStore, searchRenderer, searchUtils) {
    function execute(options) {
        var q = typeof options['q'] === "undefined" ? '' : options['q'];
        var sort = pageStore.get(pageStore.keys.selectedSortMethod, null);
        var fS = typeof options['fS'] === "undefined" ? false : options['fS'];

        var payload = {
            q: q,
            currency: window.wizzyConfig.store.currency.code,
            includeOutOfStock: window.wizzyConfig.search.configs.general.includeOutOfStock + "",
            productsCount: window.wizzyConfig.search.configs.general.noOfProducts,
            facets: searchUtils.getFacetsToAdd(),
            swatch: searchUtils.getSwatchesToAdd(),
        };

        if (sort) {
            payload['sort'] = [
                {
                    field: sort['field'],
                    order: sort['order'],
                }
            ];
        }
        pageStore.set(pageStore.keys.isPaginating, false);
        if (q !== "") {
            searchRenderer.showIndicator(false, fS);
            executeSearch(payload);
        }
        else {
            pageStore.set(pageStore.keys.searchedResponse, null);
            searchRenderer.revertDOM();
        }
    }

    function trySearchOnceConnected(payload) {
        setTimeout(function(payload) {
            executeSearch(payload);
        }, 200, payload);
    }

    function executeSearch(payload) {
        if (wizzyCommon.isConnected()) {
            var response = wizzyCommon.getClient().search(payload);
            pageStore.set(pageStore.keys.lastRequestIdSearch, response.requestId);
        }
        else {
            trySearchOnceConnected(payload);
        }
    }

    return {
        execute: execute,
    };
});