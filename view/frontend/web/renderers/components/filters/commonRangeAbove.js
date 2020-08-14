define(['jquery', 'Mustache', 'wizzy/renderers/components/filters/common', 'wizzy/utils/facets'], function($, Mustache, commonFacet, facetsUtils) {
    function getHTML(facet, type, options) {
        var data = facet.data;
        var totalReviews = data.length;
        data = data.reverse();
        var zeroVal = options['zeroVal'];

        var templates = window.wizzyConfig.search.view.templates;
        var rangeAboveTemplate = $(templates.facets.commonRangeAboveItem).html();
        var items = [];
        var count = 0;

        for (var i = 0; i < totalReviews; i++) {
            var key = data[i].key;
            var value = options['value'];
            value = value(key);
            count += data[i].count;
            var sText = "&amp; above";

            if (value == 0) {
                if (zeroVal !== null) {
                    value = zeroVal;
                }
                else {
                    value = null;
                }
            }

            if (value === null || value == options['max']) {
                continue;
            }

            var facetData = data[i].data;
            if (typeof facetData['end']) {
                delete facetData['end'];
            }
            facetData['label'] = value + options['symbol'] + " " + sText;
            var filtersData = facetsUtils.instance().addFacetFilter(facet.key, facetData);
            var htmlTemplate = Mustache.render(rangeAboveTemplate, {
                'value': value,
                'count': count,
                'key': filtersData['key'],
                'isSelected': filtersData['isSelected'],
                'sText': sText,
                'symbol': options['symbol'],
            });

            items.push({
                html: htmlTemplate,
            });
        }

        return commonFacet.getCommonHTML({
            'title': facet.label,
            'items': items,
            'key'  : facet.key,
            'withSearch': false,
            'isLeft': (type === "left"),
        });
    }

    return {
        getHTML: getHTML,
    };
});