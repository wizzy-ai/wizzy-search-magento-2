define(['wizzy/renderers/autocomplete', 'wizzy/renderers/search', 'wizzy/renderers/variation', 'wizzy/libs/pageStore'], function(autocompleteRenderer, searchRenderer, variationRenderer, pageStore) {
    return function(data) {
        if (typeof data.error !== "undefined" || data.error === null || data.error  === "") {
            return false;
        }

        var requestId = data.requestId;

        if (data.api == "autocomplete" && pageStore.get(pageStore.keys.lastRequestIdAutocomplete) == requestId) {
            autocompleteRenderer.render(data.response);
            pageStore.set(pageStore.keys.lastRequestIdAutocomplete, null);
        }

        if (data.api == "filter" && pageStore.get(pageStore.keys.lastRequestIdFilters) == requestId) {
            if (pageStore.get(pageStore.keys.filteringFor, 'page') === "menu") {
                if (typeof data.response.payload !== "undefined" && typeof data.response.payload.result !== "undefined") {
                    autocompleteRenderer.render({
                        payload: {
                            products: data.response.payload,
                        },
                        isByFilter: true
                    });
                }
            }

            if (pageStore.get(pageStore.keys.filteringFor, 'page') === "page") {
                searchRenderer.displayResults(data);
            }

            pageStore.set(pageStore.keys.lastRequestIdFilters, null);
        }

        if (data.api == "search" && pageStore.get(pageStore.keys.lastRequestIdSearch) == requestId) {
            searchRenderer.displayResults(data);
            pageStore.set(pageStore.keys.lastRequestIdSearch, null);
        }

        if (data.api == "variation") {
            variationRenderer.displayVariation(data);
        }
    }
});