define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/renderers/components/filters/common', 'wizzy/renderers/components/filters/categories', 'wizzy/renderers/components/filters/commonRangeAbove', 'wizzy/renderers/components/filters/range', 'wizzy/renderers/components/filters/swatch', 'wizzy/utils/facets'], function($, Mustache, pageStore, commonFacet, categoriesFacet, commonRangeAbove, rangeFacet, swatchFacet, facetsUtils) {
    function getHTML(facet, type) {
        if (facet.type !== "range") {
            if (facet.data.length === 0) {
                return null;
            }

            if (facet.key === "categories") {
                return categoriesFacet.getHTML(facet, type);
            }

            if (facet.key === "avgRatings") {
                return commonRangeAbove.getHTML(facet, type, {
                    'symbol': '&#x2605;',
                    'zeroVal': 0,
                    'max': 5,
                    'value' : function(value) {
                        return Math.floor(value / 20);
                    }
                });
            }

            if (facet.key === "inStock" && facet.data.length <= 1) {
                return null;
            }

            if (facet.key === "colors" || facet.key === "sizes") {
                facet.data = swatchFacet.getData(facet.data);
            }

            if (facet.key === "discountPercentage") {
                return commonRangeAbove.getHTML(facet, type, {
                    'symbol': '%',
                    'zeroVal': 1,
                    'max': 100,
                    'value' : function(value) {
                        return value;
                    }
                });
            }

            return commonFacet.getCommonHTML({
                'title': facet.label,
                'items': getItemsHTML(facet.key, facet.data),
                'key'  : facet.key,
                'withSearch': (facet.data.length > 15 && type === "left"),
                'isLeft': (type === "left"),
            });
        }
        else {
            return rangeFacet.getHTML(facet, type);
        }
    }

    function getItemsHTML(facetKey, items) {
        var totalItems = items.length;
        var templates = window.wizzyConfig.search.view.templates;
        var facetItemTemplate = $(templates.facets.item).html();

        var itemsHtml = [];

        for(var i =0; i < totalItems; i++) {
            var filtersData = facetsUtils.instance().addFacetFilter(facetKey, items[i]);

            itemsHtml.push({
                html: Mustache.render(facetItemTemplate, {
                    'label': items[i].label,
                    'count': items[i].count,
                    'isSwatch': items[i].isSwatch,
                    'isVisualSwatch': items[i].isVisualSwatch,
                    'isURLSwatch': items[i].isURLSwatch,
                    'swatchValue': items[i].swatchValue,
                    'key': filtersData['key'],
                    'isSelected': filtersData['isSelected'],
                })
            });
        }

        return itemsHtml;
    }

    return {
        getHTML: getHTML,
    };
});