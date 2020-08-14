define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'noUiSlider', 'wNumb', 'wizzy/renderers/components/filters/common', 'wizzy/utils/facets', 'wizzy/utils/filters'], function($, Mustache, pageStore, noUiSlider, wNumb, commonFacet, facetsUtils, filtersUtils) {
    function getHTML(facet, type) {
        var templates = window.wizzyConfig.search.view.templates;
        var rangeTemplate = $(templates.facets.rangeItem).html();
        var items = [];

        if (facet.data['max'] - facet.data['min'] > 1) {

            var min = facet.data['min'];
            var max = facet.data['max'];

            var selectedMinMax = getSelectedMinMax(max, min, facet.key);

            var selectedMin = selectedMinMax['min'];
            var selectedMax = selectedMinMax['max'];

            if (selectedMin != min || selectedMax != max) {
                var rangeData = {};

                if (selectedMin != min) {
                    rangeData["min"] = selectedMin;
                }
                if (selectedMax != min) {
                    rangeData["max"] = selectedMax;
                }
                rangeData['label'] = getSelectedFilterLabel(rangeData);
                facetsUtils.instance().addFacetFilter(facet.key, rangeData);
            }

            items.push({
                html: Mustache.render(rangeTemplate, {
                    'label': facet.label,
                    'min' : facet.data['min'],
                    'max' : facet.data['max'],
                    'avg' : facet.data['avg'],
                }),
            });

            return commonFacet.getCommonHTML({
                'title': facet.label,
                'items': items,
                'key'  : facet.key,
                'withSearch': false,
                'isLeft': (type === "left"),
            });
        }

        return null;
    }

    function refreshRanges() {
        $('.wizzy-facet-range-slider').each(function(index, item) {
            var min = $(item).data('min');
            var max = $(item).data('max');
            var avg = $(item).data('avg');
            var mergingTooltipSlider = document.getElementById('merging-tooltips');
            var element = $(this)[0];
            var jElement = $(this);

            var facetKey = jElement.parents('.wizzy-filters-facet-block').data('key');
            var selectedMinMax = getSelectedMinMax(max, min, facetKey);

            var selectedMin = selectedMinMax['min'];
            var selectedMax = selectedMinMax['max'];

            noUiSlider.create(element, {
                start: [selectedMin, selectedMax],
                connect: true,
                tooltips: [getRangeValueFormatter(), getRangeValueFormatter()],
                step: 1,
                range: {
                    'min': min,
                    'max': max,
                },
                pips: {
                    mode: 'positions',
                    values: [0, 100],
                    density: 1,
                    format: getRangeValueFormatter(),
                }
            });

            element.noUiSlider.on('change', function (data) {
                var key = jElement.parents('.wizzy-filters-facet-block').data('key');
                var rangeData = {
                    max: data[1],
                    min: data[0],
                };
                if (max == rangeData['max']) {
                    delete rangeData['max'];
                }
                if (min == rangeData['min']) {
                    delete rangeData['min'];
                }
                rangeData['label'] = getSelectedFilterLabel(rangeData);
                var filtersData = facetsUtils.instance().addFacetFilter(key, rangeData);
                $.fn.clearWizzyFilters(key);
                $.fn.applyWizzyFilters(key, filtersData['key'], true);
            });
        });
    }

    function getSelectedMinMax(max, min, facetKey) {
        var currentFilters = filtersUtils.getFilters();
        var selectedMax = max;
        var selectedMin = min;

        if (typeof currentFilters[facetKey] !== "undefined" && Array.isArray(currentFilters[facetKey]) && currentFilters[facetKey].length > 0) {
            if (typeof currentFilters[facetKey][0]['gte'] !== "undefined") {
                selectedMin = currentFilters[facetKey][0]['gte'];
            }

            if (typeof currentFilters[facetKey][0]['lte'] !== "undefined") {
                selectedMax = currentFilters[facetKey][0]['lte'];
            }

            if (selectedMax > max) {
                selectedMax = max;
            }

            if (selectedMin < min) {
                selectedMin = min;
            }
        }

        return {
            max: selectedMax,
            min: selectedMin
        };
    }

    function getSelectedFilterLabel(data) {
        var label = "";
        if (typeof data['min'] !== "undefined" && typeof data['max'] !== "undefined") {
            label = valueWithSymbol(data['min']) + " - " + valueWithSymbol(data['max']);
        }
        else if (typeof data['min'] !== "undefined") {
            label = "&gt; "  + valueWithSymbol(data['min']);
        }
        else if (typeof data['max'] !== "undefined") {
            label = "&lt; " + valueWithSymbol(data['max']);
        }

        return label;
    }

    function valueWithSymbol(value) {
        return window.wizzyConfig.store.currency.symbol + value;
    }

    function getRangeValueFormatter() {
        return wNumb({
            decimals: 2,
            prefix: window.wizzyConfig.store.currency.symbol,
        });
    }

    return {
        getHTML: getHTML,
        refreshRanges: refreshRanges,
    };
});