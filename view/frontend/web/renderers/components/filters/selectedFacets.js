define(['jquery', 'Mustache', 'wizzy/utils/facets', 'wizzy/renderers/components/filters/swatch'], function($, Mustache, facetsUtils, swatchUtils) {
    function getHTML() {
        var selectedFacets = facetsUtils.instance().getSelectedFacets();
        selectedFacets = getSelectedFacetsList(selectedFacets);
        var facetItems = getFacetItemsHTML(selectedFacets);

        if (facetItems.length === 0) {
            return null;
        }

        var templates = window.wizzyConfig.search.view.templates;
        var selectedFacetsCommon = $(templates.facets.selectedCommon).html();

        return Mustache.render(selectedFacetsCommon, {
            items: facetItems
        });
    }

    function getFacetItemsHTML(selectedFacets) {
        var totalSelectedFacets = selectedFacets.length;
        var items = [];
        var templates = window.wizzyConfig.search.view.templates;
        var selectedFacetTemplate = $(templates.facets.selectedItem).html();

        for (var i = 0; i < totalSelectedFacets; i++) {
            items.push({
                html: Mustache.render(selectedFacetTemplate, selectedFacets[i]),
            });
        }

        return items;
    }

    function getSelectedFacetsList(selectedFacets) {
        var facetKeys = Object.keys(selectedFacets);
        var totalSelectedFacets = facetKeys.length;
        var selectedFacetsList = [];

        for (var i = 0; i < totalSelectedFacets; i++) {
            var key = facetKeys[i];
            var facetFilterKeys = Object.keys(selectedFacets[key]);
            var totalFilters = facetFilterKeys.length;

            for (var j = 0; j < totalFilters; j++) {
                var filterKey = facetFilterKeys[j];
                var selectedFilter = selectedFacets[key][filterKey];

                var label = selectedFilter['label'];
                if (typeof selectedFilter['facetData'] !== "undefined" && typeof selectedFilter['facetData']['parentkey'] !== "undefined" && selectedFilter['facetData']['parentkey'] !== "") {
                    if (typeof selectedFilter['facetData']['label'] !== "undefined" && selectedFilter['facetData']['label'] !== "") {
                        label = selectedFilter['facetData']['label'] + ": " + label;
                    }
                }

                if (typeof label === "undefined" || label === "" || label === null) {
                    continue;
                }

                var selectedFacet = {
                    'label': label,
                    'key'  : filterKey,
                    'isSwatch': false,
                    'facetKey': key,
                };

                if (typeof selectedFilter['optionData'] !== "undefined") {
                    var swatchData = swatchUtils.getSwatchData(selectedFilter['optionData']);

                    selectedFacet["isSwatch"] = swatchData['isSwatch'];
                    selectedFacet["isVisualSwatch"] = swatchData['isVisualSwatch'];
                    selectedFacet["isURLSwatch"] = swatchData['isURLSwatch'];
                    selectedFacet["swatchValue"] = swatchData['swatchValue'];
                }

                selectedFacetsList.push(selectedFacet);
            }
        }

        return selectedFacetsList;
    }

    return {
        getHTML: getHTML,
    };
});