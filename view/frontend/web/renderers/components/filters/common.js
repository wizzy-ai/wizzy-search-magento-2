define(['jquery', 'Mustache'], function($, Mustache) {
    function getCommonHTML(options) {

        if (options['items'].length === 0) {
            return null;
        }

        var templates = window.wizzyConfig.search.view.templates;
        var facetTemplate = $(templates.facets.common).html();
        facetTemplate = Mustache.render(facetTemplate, {
            'title': options['title'],
            'items': options['items'],
            'key'  : options['key'],
            'withSearch': options['withSearch'],
            'isLeft' : options['isLeft'],
            'divKey' : options['key'].replace(" ", "")
        });

        return facetTemplate;
    }

    return {
        getCommonHTML: getCommonHTML,
    };
});