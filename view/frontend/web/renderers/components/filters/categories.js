define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'underscore', 'wizzy/renderers/components/filters/common', 'wizzy/utils/facets'], function($, Mustache, pageStore, _, commonFacet, facetsUtils) {

    var renderingFacet = "";

    function getHTML(facet, type) {
        renderingFacet = facet;
        var itemsHTML = "";
        var withSearch = false;

        if (window.wizzyConfig.search.configs.facets.categoryDisplay === "list") {
            itemsHTML = getSingleListHTML(facet.data);
            withSearch = (facet.data.length > 15);
        }
        else if (window.wizzyConfig.search.configs.facets.categoryDisplay === "hierarchy") {
            var categoriesHierarchy = getCategoriesHierarchy(facet.data);
            categoriesHierarchy = sortChildrenByLevel(categoriesHierarchy);
            itemsHTML = getHierarchyHTML(categoriesHierarchy);
        }
        return commonFacet.getCommonHTML({
            'title': facet.label,
            'items': itemsHTML,
            'key'  : facet.key,
            'withSearch': withSearch,
            'isLeft': (type === "left"),
        });
    }

    function getSingleListHTML(items) {
        var totalItems = items.length;
        var templates = window.wizzyConfig.search.view.templates;
        var facetItemTemplate = $(templates.facets.item).html();

        var itemsHtml = [];

        for(var i =0; i < totalItems; i++) {
            var filtersData = facetsUtils.instance().addFacetFilter(renderingFacet.key, items[i]);

            itemsHtml.push({
                html: Mustache.render(facetItemTemplate, {
                    'label': items[i].label,
                    'count': items[i].count,
                    'isSelected': filtersData['isSelected'],
                    'key'  : filtersData['key'],
                })
            });
        }

        return itemsHtml;
    }

    function getHierarchyHTML(categoriesHierarchy) {
        var keys = Object.keys(categoriesHierarchy);
        var totalKeys = keys.length;
        var itemsHTML = [];

        for(var i=0; i<totalKeys; i++) {
            var category = categoriesHierarchy[keys[i]];
            if (category.isParent === true) {
                var itemHTML = getItemHTML(category, categoriesHierarchy);
                itemsHTML.push({
                    'html': itemHTML
                });
            }
        }

        return itemsHTML;
    }

    function getItemHTML(category, categoriesHierarchy) {
        var childrenHTML = "";
        var templates = window.wizzyConfig.search.view.templates;
        var categoryTemplate = $(templates.facets.categoryItem).html();

        var filtersData = facetsUtils.instance().addFacetFilter(renderingFacet.key, category['data']);

        if (category.children.length > 0) {
            childrenHTML = getChildrenHTML(category.children, categoriesHierarchy);
            categoryTemplate = Mustache.render(categoryTemplate, {
                'label': category.data.label,
                'count': category.data.count,
                'key' : filtersData['key'],
                'isSelected': (filtersData['isSelected'] || childrenHTML.indexOf('active') >= 0),
                'childrenHTML': childrenHTML,
            });
        }
        else {
            categoryTemplate = Mustache.render(categoryTemplate, {
                'label': category.data.label,
                'count': category.data.count,
                'key' : filtersData['key'],
                'isSelected': filtersData['isSelected'],
                'childrenHTML': childrenHTML,
            });
        }

        return categoryTemplate;
    }

    function getChildrenHTML(children, categoriesHierarchy) {
        var totalChildren = children.length;
        var childrenHTML = "";
        for (var i = 0; i < totalChildren; i++) {
            childrenHTML += getItemHTML(categoriesHierarchy[children[i].id], categoriesHierarchy);
        }

        return childrenHTML;
    }

    function sortChildrenByLevel(categoriesHierarchy) {
        var keys = Object.keys(categoriesHierarchy);
        var totalKeys = keys.length;

        for(var i=0; i<totalKeys; i++) {
            var id = keys[i];
            var children = categoriesHierarchy[id]['children'];
            if (children.length > 0) {
                children = _.sortBy(children, function(childCategory) {
                    return childCategory['level'];
                });
                categoriesHierarchy[id]['children'] = children;
            }
        }

        return categoriesHierarchy;
    }

    function getCategoriesHierarchy(categories) {
        var totalCategories = categories.length;
        var categoriesHierarchy = {};

        for (var i = 0; i < totalCategories; i++) {
            categoriesHierarchy[categories[i].data.id] = {
                'data'    : categories[i],
                'children': [],
                'isParent': true,
            };
        }

        for (i = 0; i < totalCategories; i++) {
            if (typeof categoriesHierarchy[categories[i].data.parentId] !== "undefined") {
                categoriesHierarchy[categories[i].data.parentId]['children'].push(categories[i].data);
                categoriesHierarchy[categories[i].data.id]['isParent'] = false;
            }
        }

        return categoriesHierarchy;
    }

    return {
        getHTML: getHTML,
    };
});