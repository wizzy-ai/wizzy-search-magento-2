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

    function getProductClickDOM() {
        if (typeof window.wizzyConfig.analytics.configs.general.clickDom !== 'undefined'){
            return window.wizzyConfig.analytics.configs.general.clickDom
        }
    
        return '.wizzy-result-product a'
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

    function getMinQueryLength() {
        if (typeof window.wizzyConfig.search.configs.general.minQueryLength !== "undefined") {
            return window.wizzyConfig.search.configs.general.minQueryLength;
        }
        return 3;
    }
    
    return {
        getFacetsToAdd: getFacetsToAdd,
        getSwatchesToAdd: getSwatchesToAdd,
        getInputDOM: getInputDOM,
        getMinQueryLength: getMinQueryLength,
        getProductClickDOM:getProductClickDOM,
    };

});