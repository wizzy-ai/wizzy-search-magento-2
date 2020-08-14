define(['jquery', 'Mustache', 'wizzy/libs/pageStore'], function($, Mustache, pageStore) {

    function getHTML() {
        var response = pageStore.get(pageStore.keys.searchedResponse, null);
        var searchedQuery =  pageStore.get(pageStore.keys.searchInputValue, null);

        var total = 0;
        if (response !== null) {
            total = response['total'];
        }

        var templates = window.wizzyConfig.search.view.templates;
        var summaryTemplate = $(templates.summary).html();
        summaryTemplate = Mustache.render(summaryTemplate, {
            'total': total,
            'query': searchedQuery,
        });

        return summaryTemplate;
    }

    return {
        getHTML: getHTML,
    };
});