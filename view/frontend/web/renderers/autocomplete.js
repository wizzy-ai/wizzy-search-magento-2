define(['jquery', 'Mustache', 'underscore', 'wizzy/libs/pageStore', 'wizzy/renderers/components/search/products'], function($, Mustache, _, pageStore, productsComponent) {
    var wizzyAutoCompleteConfig;
    var isByFilter = false;

    function render(data) {
        wizzyAutoCompleteConfig = window.wizzyConfig.autocomplete.menu.view;
        isByFilter = (typeof data["isByFilter"] !== "undefined" && data["isByFilter"])  ? true: false;

        if (typeof data["payload"] !== "undefined") {
            resetAutosuggestionStore();
            var isMenuHidden = require('wizzy/libs/autocomplete').isMenuHidden();
            var suggestionsTemplate = getSuggestionsHTML(data["payload"]);
            var topProductsTemplate = getProductsHTML(data["payload"]);
            var autocompleteWrapper = $(wizzyAutoCompleteConfig.templates.wrapper).html();

            if ((suggestionsTemplate == "" && !isByFilter) && topProductsTemplate == "" && window.wizzyConfig.autocomplete.menu.noResultsBehaviour == "hide_menu") {
                require('wizzy/libs/autocomplete').hideMenu();
                return;
            }

            if (!isByFilter) {
                autocompleteWrapper = Mustache.render(autocompleteWrapper, {
                    suggestions: suggestionsTemplate['html'],
                    isMenuHidden: isMenuHidden,
                    topProducts: topProductsTemplate,
                    hasSuggestions: suggestionsTemplate['hasSuggestions'],
                });
                $(wizzyAutoCompleteConfig.wrapper).html(autocompleteWrapper);
            }
            else {
                if (topProductsTemplate != "") {
                    if ($(wizzyAutoCompleteConfig.topProductsBlock).size() > 0) {
                        $(wizzyAutoCompleteConfig.topProductsBlock).replaceWith($.parseHTML(topProductsTemplate));
                    }
                    else {
                        $(wizzyAutoCompleteConfig.menu).removeClass('withoutTopProducts');
                        $(wizzyAutoCompleteConfig.menu).append($.parseHTML(topProductsTemplate));
                    }
                }
                else {
                    $(wizzyAutoCompleteConfig.topProductsBlock).remove();
                    $(wizzyAutoCompleteConfig.menu).addClass('withoutTopProducts');
                }
            }

            require('wizzy/libs/autocomplete').showMenu();
        }

    }
    function setElement(element) {
        input = element;
    }

    function resetAutosuggestionStore() {
        if (!isByFilter) {
            pageStore.set(pageStore.keys.suggestionFilters, []);
        }
    }

    function getProductsHTML(data) {
        var products = getProductsList(data);
        if (products.length == 0) {
            return '';
        }

        var topProductsTemplate = $(wizzyAutoCompleteConfig.templates.products).html();
        topProductsTemplate = Mustache.render(topProductsTemplate, {
            products: products,
            hasCategories: hasProductCategories,
            category: getProductCategory,
        });
        return topProductsTemplate;
    }

    function hasProductCategories() {
        if (typeof this.categories !== "undefined" && this.categories.length > 0) {
            return true;
        }

        return false;
    }

    function getProductCategory() {
        var totalCategories = this.categories.length;
        var categoryName = "";
        if (totalCategories > 0) {
            categoryName = this.categories[totalCategories - 1].name;
        }

        return categoryName;
    }

    function getProductsList(data) {
        var products = [];
        if (typeof data['products'] !== "undefined" && typeof data['products']['result'] !== "undefined") {
            products = data['products']['result'];
        }

        return productsComponent.getTransformedProducts(products);
    }

    function getSuggestionsHTML(data) {
        if (isByFilter) {
            return '';
        }
        var suggestions = getSuggestionList(data);
        var suggestionsTemplate = $(wizzyAutoCompleteConfig.templates.suggestions).html();
        suggestionsTemplate = Mustache.render(suggestionsTemplate, {
            suggestions: suggestions,
            hasSuggestions: (suggestions.length > 0)
        });
        return {
            html: suggestionsTemplate,
            hasSuggestions: (suggestions.length > 0)
        };
    }

    function getSuggestionList(data) {

        var suggestionGroups = [
            "categories",
            "others",
            "pages",
        ];
        var totalSuggestionGroups = suggestionGroups.length;

        var suggestions = [];
        var suggestionFilters = [];
        var suggestionIndex = 0;

        for(var i = 0; i < totalSuggestionGroups; i++) {
            var groupKey = suggestionGroups[i];
            var groupsData = typeof data[groupKey] !== "undefined" ? data[groupKey] : [];
            if (groupKey == 'categories' && groupsData.length == 0) {
                groupsData = getCategoriesFromOthers(data['others']);
            }

            if (groupsData.length > 0) {
                suggestions.push(getSuggestionItem(groupKey, 'head', i));
                var totalGroupsData = groupsData.length;
                for(var j = 0; j < totalGroupsData; j++) {
                    suggestions.push(getSuggestionItem(groupsData[j], groupKey, suggestionIndex));
                    suggestionFilters.push({
                        group: groupKey,
                        value: groupsData[j].value,
                        filters: getSuggestionFilter(groupsData[j]),
                    });
                    suggestionIndex++;
                }
            }
        }
        pageStore.set(pageStore.keys.suggestionFilters, suggestionFilters);

        return suggestions;
    }

    function getCategoriesFromOthers(others) {
        if (typeof others === "undefined" || others.length === 0) {
            return [];
        }

        var totalOthersData = others.length;
        var addedCategories = {};
        var suggestions = [];

        for (var i = 0; i<totalOthersData ; i++) {
            var data = others[i];
            var relatedCats = getRelatedCategories(data);
            var totalRelatedCats = relatedCats.length;
            if (totalRelatedCats > 0) {
                var category = relatedCats[totalRelatedCats - 1];
                if (typeof addedCategories[category.id] !== "undefined") {
                    continue;
                }
                var categorySuggestion = {};
                categorySuggestion['value'] = category.name;
                categorySuggestion['valueHighlighted'] = "<em>" + category.name + "</em>";
                categorySuggestion['filters'] = {
                    'categories': [category.id],
                };
                categorySuggestion['payload'] = data["payload"];
                addedCategories[category.id] = true;
                suggestions.push(categorySuggestion);
            }

            if (suggestions.length === 2) {
                break;
            }
        }

        return suggestions;
    }

    function getRelatedCategories(data) {
        var payload = (typeof data["payload"] !== "undefined") ? data["payload"] : [];
        var totalPayload = payload.length;

        var payloadObj = {
            relatedCategories: [],
        };

        for (var i = 0; i < totalPayload; i++) {
            if (typeof payloadObj[payload[i].key] !== "undefined") {
                payloadObj[payload[i].key] = payload[i].value;
            }
        }

        return payloadObj['relatedCategories'];
    }

    function getSuggestionFilter(data) {
        var filters = data.filters;
        return filters;
    }

    function getHeadLabel(groupKey) {
        var groupLabels= {
          "categories": window.wizzyConfig.autocomplete.menu.categories.title,
          "others": window.wizzyConfig.autocomplete.menu.others.title,
          "pages": window.wizzyConfig.autocomplete.pages.title,
        };

        return (typeof groupLabels[groupKey] !== "undefined" ? groupLabels[groupKey] : "");
    }

    function getSuggestionItem(data, type, index) {
        if (type === "head") {
            return {
                'label' : getHeadLabel(data),
                'group' : type,
                'isHead': true,
                'hasLabelPath': false,
                'searchTerm': '',
                'index': index
            };
        }
        var labelPath = getSubLabelPath(data, type);
        return {
            'label' : data.valueHighlighted,
            'labelPath' : labelPath,
            'hasLabelPath' : (labelPath.length > 0) ? true: false,
            'group' : type,
            'isHead': false,
            'index': index,
            'searchTerm': data.value.toLowerCase(),
        };
    }

    function getSubLabelPath(data, type) {
        if (type == "others") {
            return [];
        }
        var payload = (typeof data["payload"] !== "undefined") ? data["payload"] : [];
        var toalPayload = payload.length;
        var payloadObj = {
            categoryId: 0,
            parentId: 0,
            relatedCategories: [],
        };
        var subLabelPath =  [];

        for (var i = 0; i < toalPayload; i++) {
            if (typeof payloadObj[payload[i].key] !== "undefined") {
                payloadObj[payload[i].key] = payload[i].value;
            }
        }

        if (payloadObj.categoryId != 0) {
            var categorypath = [];

            var totalRealtedCats = payloadObj['relatedCategories'].length;
            var totalCatsInPath = "";
            for(var i = 0; i < totalRealtedCats; i++) {
                if (payloadObj['relatedCategories'][i]['id'] != payloadObj.categoryId) {
                    categorypath.push(payloadObj['relatedCategories'][i]);
                    totalCatsInPath++;
                }
            }

            categorypath = _.sortBy(categorypath, function(category) {
                return category['level'];
            });

            for (var i =0; i < totalCatsInPath ; i++) {
                subLabelPath.push({
                    'label': categorypath[i].name,
                    'image': categorypath[i].image,
                    'description': categorypath[i].description
                });
            }
        }

        return subLabelPath;
    }

    return {
        render: render,
    };
});