define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/renderers/components/search/products', 'wizzy/utils/pagination'], function($, Mustache, pageStore, productsComponent, paginationUtils) {
    function getHTML() {
        var templates = window.wizzyConfig.search.view.templates;
        var resultsTemplate = $(templates.results).html();

        var response = pageStore.get(pageStore.keys.searchedResponse, null);

        var products = [];
        if (response !== null) {
            products = response['result'];
        }

        resultsTemplate = Mustache.render(resultsTemplate, {
            products: getProductsHTML(products)
        });
        return resultsTemplate;
    }

    function getProductsHTML(products) {
        var productsHTML = [];
        var transformedProducts = productsComponent.getTransformedProducts(products);
        var totalProducts = transformedProducts.length;

        var templates = window.wizzyConfig.search.view.templates;
        var productTemplate = $(templates.product).html();

        for (var i = 0; i < totalProducts; i++) {
            productsHTML.push({
                html: Mustache.render(productTemplate, transformedProducts[i])
            });
        }

        return productsHTML;
    }

    function getEmptyHTML() {
        var templates = window.wizzyConfig.search.view.templates;
        var emptyTemplate = $(templates.emptyResults).html();
        var searchedQuery =  pageStore.get(pageStore.keys.searchInputValue, null);
        var lastRequestId = pageStore.get(pageStore.keys.lastRequestIdFilters, null);
        if (typeof lastRequestId['page'] === "undefined") {
            lastRequestId = pageStore.get(pageStore.keys.lastRequestIdSearch, null);
        } else {
            lastRequestId = lastRequestId['page'];
        }

        return Mustache.render(emptyTemplate, {
            'query': searchedQuery,
            'lastRequestId': lastRequestId,
        });
    }

    function hasProducts() {
        var response = pageStore.get(pageStore.keys.searchedResponse, null);

        if (typeof response !== "undefined" && response !== null && typeof response['result'] !== "undefined" && response['result'].length > 0) {
            return true;
        }

        return false;
    }

    function hasMoreResults() {
        var response = pageStore.get(pageStore.keys.searchedResponse, null);
        if (response !== null) {
            var currentPage = paginationUtils.getCurrentPage();
            if (typeof response['pages'] !== "undefined" && response['pages'] < currentPage) {
                return true;
            }

            var products = [];
            if (response !== null) {
                products = response['result'];
            }

            if (products.length == window.wizzyConfig.search.configs.general.noOfProducts) {
                return true;
            }
        }

        return false;
    }

    return {
        getHTML: getHTML,
        getEmptyHTML: getEmptyHTML,
        getProductsHTML: getProductsHTML,
        hasProducts: hasProducts,
        hasMoreResults: hasMoreResults,
    };
});