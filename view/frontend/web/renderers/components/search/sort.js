define(['jquery', 'Mustache', 'wizzy/libs/pageStore'], function($, Mustache, pageStore) {

    function getHTML() {
        var sort = window.wizzyConfig.search.configs.sorts.configs;
        if (typeof sort === "undefined" || sort === null || sort.length <= 1 ) {
            return null;
        }

        var templates = window.wizzyConfig.search.view.templates;
        var sortTemplate = $(templates.sort).html();
        sortTemplate = Mustache.render(sortTemplate, {
            options: getSortOptionsArray(sort),
        });

        return sortTemplate;
    }

    function getSortOptionsArray(sort) {
        var keys = Object.keys(sort);
        var totalOptions = keys.length;

        var selectedSort = pageStore.get(pageStore.keys.selectedSortMethod, null);
        var selectedSortField = (selectedSort !== null && typeof selectedSort['field'] !== "undefined") ? selectedSort['field'] : null;
        var selectedSortOrder = (selectedSort !== null && typeof selectedSort['order'] !== "undefined") ? selectedSort['order'] : null;

        var options = [];
        for (var i = 0; i < totalOptions; i++) {
            var sortOption = sort[keys[i]];
            sortOption['isSelected'] = (selectedSortField === sortOption['field'] && selectedSortOrder === sortOption['order']);
            options.push(sortOption);
        }

        return options;
    }

    return {
        getHTML: getHTML,
    };
});