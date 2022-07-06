define(['jquery', 'wizzy/renderers/autocomplete', 'wizzy/libs/pageStore'], function($, autocompleteRenderer, pageStore){
   function render(data) {
        var displayingFor = data.for;
        if (displayingFor === "defaultMenu") {
            var autocompleteResponse = {'payload': {},'isForDefault': true, 'element': data.element};
            var defaultBehaviour = window.wizzyConfig.autocomplete.configs.defaultBehaviour;
            if (defaultBehaviour.suggestions.enabled) {
                var pool = defaultBehaviour.suggestions.defaultPool;
                var totalSuggestions = pool.length;

                for (var i = 0; i < totalSuggestions; i++) {
                    var suggestion = pool[i];
                    if (typeof autocompleteResponse['payload'][suggestion['section']] === "undefined") {
                        autocompleteResponse['payload'][suggestion['section']] = [];
                    }
                    autocompleteResponse['payload'][suggestion['section']].push(suggestion);
                }
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
        render: render,
    };
});