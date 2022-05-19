define([''], function() {

    function getFacetsToAdd() {
        var facetsConfig = window.wizzyConfig.search.configs.facets.configs;
        var facets = [];
        if (typeof facetsConfig !== "undefined") {
            var keys = Object.keys(facetsConfig);
            var totalFacets = keys.length;
            for (var i = 0; i < totalFacets; i++) {
                var facetToAdd = facetsConfig[keys[i]];
                facetToAdd['order'] = i;
                facets.push(facetToAdd);
            }

            return facets;
        }

        return [];
    }

    function getInputDOM() {
        if (typeof window.wizzyConfig.search.input.dom !== "undefined") {
            return window.wizzyConfig.search.input.dom;
        }
        
        return '.wizzy-search-input';
    }

    function getSwatchesToAdd() {
        var swatchesConfig = window.wizzyConfig.search.configs.swatches.configs;
        var swatches = [];
        if (typeof swatchesConfig !== "undefined") {
            var keys = Object.keys(swatchesConfig);
            var totalSwatches = keys.length;
            for (var i = 0; i < totalSwatches; i++) {
                var swatchToAdd = swatchesConfig[keys[i]];
                swatches.push(swatchToAdd);
            }

            return swatches;
        }

        return swatches;
    }

    return {
        getFacetsToAdd: getFacetsToAdd,
        getSwatchesToAdd: getSwatchesToAdd,
        getInputDOM: getInputDOM,
    };

});