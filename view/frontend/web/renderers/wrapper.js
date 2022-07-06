define(['wizzy/renderers/autocomplete', 'wizzy/renderers/search', 'wizzy/renderers/variation', 'wizzy/libs/pageStore', 'wizzy/common'], function(autocompleteRenderer, searchRenderer, variationRenderer, pageStore, wC) {
    return function(data) {
        if (typeof data.error !== "undefined" || data.error === null || data.error  === "") {
            return false;
        }

        var requestId = data.requestId;
        var responseId = data.responseId;
        pageStore.set(pageStore.keys.lastResponseId, responseId);

        if (data.api == "autocomplete" && pageStore.get(pageStore.keys.lastRequestIdAutocomplete) == requestId) {
            autocompleteRenderer.render(data.response);
            pageStore.set(pageStore.keys.lastRequestIdAutocomplete, null);
        }

        if (data.api == "filter") {
            var filterRequests = pageStore.get(pageStore.keys.filterRequests, {});
            var lastRequestIdFilters =  pageStore.get(pageStore.keys.lastRequestIdFilters, {});

            var filteringFor = filterRequests[requestId];
    
            if (typeof lastRequestIdFilters[filteringFor] !== "undefined") {
                if (filteringFor === "menu") {
                    if (typeof data.response.payload !== "undefined" && typeof data.response.payload.result !== "undefined") {
                        autocompleteRenderer.render({
                            payload: {
                                products: data.response.payload,
                            },
                            isByFilter: true
                        });
                    }
                }
            }

          if (filteringFor === "page") {
                 searchRenderer.displayResults(data);
            }
 
            if (filteringFor === "defaultMenu") {
                var groupedFilteredProducts = pageStore.get(pageStore.keys.groupedFilteredProducts, {});
                groupedFilteredProducts[filteringFor] = data.response;
                pageStore.set(pageStore.keys.groupedFilteredProducts, groupedFilteredProducts);
                wC.sessionDataStorage.setGroupedFilteredProducts(groupedFilteredProducts);
            }
            delete lastRequestIdFilters[filteringFor];
            pageStore.set(pageStore.keys.lastRequestIdFilters, lastRequestIdFilters);
        }

        if (data.api == "search" && pageStore.get(pageStore.keys.lastRequestIdSearch) == requestId) {
            searchRenderer.displayResults(data);
            pageStore.set(pageStore.keys.lastRequestIdSearch, null);
        }

        if (data.api == "variation") {
            variationRenderer.displayVariation(data);
        }

        wizzy.triggerEvent(wizzy.allowedEvents.VIEW_RENDERED, {
            data: data,
        });
    }
});
