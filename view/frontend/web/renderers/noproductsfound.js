define(['jquery', 'wizzy/renderers/dom', 'wizzy/renderers/search', 'wizzy/renderers/components/search/results'], function($, domRenderer, searchRenderer, resultsComponent) {
    function showProductsWithFailedSearch() {
        let noProductsFoundDOM = $(domRenderer.getNoProductsFoundDOM());
        let noResultsWithProducts = resultsComponent.getEmptyHTML();
        $(noProductsFoundDOM).html(noResultsWithProducts);
    }
    return {
        showProductsWithFailedSearch: showProductsWithFailedSearch
    };
});