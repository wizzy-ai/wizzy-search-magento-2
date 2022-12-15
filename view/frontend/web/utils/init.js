requirejs(['wizzy/renderers/dom', 'wizzy/renderers/search', 'wizzy/renderers/noProductsFound', 'wizzy/fetchers/filters'], function(domRenderer, searchRenderer, noProductsFoundRenderer, filtersFetcher) {
    wizzy.registerEvent(wizzy.allowedEvents.EMPTY_RESULTS_RENDERED, function(payload) {
        if (window.wizzyConfig.search.configs != undefined && window.wizzyConfig.search.configs.noProductsFound) {
            var noProductsFoundDefaultBehaviour = window.wizzyConfig.search.configs.noProductsFound
        }
        if (window.wizzyConfig.pageStore.groupedFilteredProducts != undefined && window.wizzyConfig.pageStore.groupedFilteredProducts.noProductsFound) {
            var defaultData = window.wizzyConfig.pageStore.groupedFilteredProducts.noProductsFound;
        }
            if (defaultData != undefined && noProductsFoundDefaultBehaviour != undefined  &&noProductsFoundDefaultBehaviour.showProducts) {
                searchRenderer.showLoaderForSpecificDOM(domRenderer.getNoProductsFoundDOM());
                if(!defaultData) {
                    var defaultPool = noProductsFoundDefaultBehaviour.defaultPool;
                    if (defaultPool.method === "filters") {
                        filtersFetcher.execute({
                            'for': 'noProductsFound',
                            'filters': defaultPool.data,
                        });
                    }
                } else {
                    noProductsFoundRenderer.showProductsWithFailedSearch();
                }                
            }
            return payload;
    });

});
