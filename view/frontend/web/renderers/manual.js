define(['jquery', 'wizzy/renderers/autocomplete', 'wizzy/libs/pageStore', 'wizzy/utils/autocomplete'], function($, autocompleteRenderer, pageStore, autocompleteUtils) {
    function manualRender(data){
        var displayingFor = data.for;
        if (displayingFor === "defaultMenu") {
            var autocompleteResponse = {'payload': {},'isForDefault': true, 'element': data.element};
            var defaultBehaviour = window.wizzyConfig.autocomplete.configs.defaultBehaviour;
            if (defaultBehaviour.suggestions.enabled) {
                autocompleteResponse['payload'] = autocompleteUtils.getDefaultSuggestions();
            }

            if (defaultBehaviour.topProducts.enabled) {
                var groupedFilteredProducts = pageStore.get(pageStore.keys.groupedFilteredProducts, {});
                if (typeof groupedFilteredProducts[displayingFor] !== "undefined" && typeof groupedFilteredProducts[displayingFor]['payload'] !== "undefined") {
                    autocompleteResponse['payload']['products'] = groupedFilteredProducts[displayingFor]['payload'];
                }
            }

            autocompleteRenderer.render(autocompleteResponse);
        }
    }

    return {
       manualRender:manualRender,
    }
    
});