requirejs(['wizzy/renderers/dom', 'wizzy/renderers/search', 'wizzy/renderers/noProductsFound', 'wizzy/fetchers/filters'], function(domRenderer, searchRenderer, noProductsFoundRenderer, filtersFetcher) {
    wizzy.registerEvent(wizzy.allowedEvents.EMPTY_RESULTS_RENDERED, function(payload) {

        if (typeof window.wizzyConfig.search.configs != 'undefined' && window.wizzyConfig.search.configs.noProductsFound) {
            var noProductsFoundDefaultBehaviour = window.wizzyConfig.search.configs.noProductsFound
        }

        var defaultData = typeof window.wizzyConfig.pageStore.groupedFilteredProducts !== 'undefined' ? window.wizzyConfig.pageStore.groupedFilteredProducts.noProductsFound : null;
            
        if (typeof noProductsFoundDefaultBehaviour !== 'undefined' && noProductsFoundDefaultBehaviour.showProducts) {
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
