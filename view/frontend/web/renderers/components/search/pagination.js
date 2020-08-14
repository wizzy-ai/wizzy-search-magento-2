define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/utils/pagination'], function($, Mustache, pageStore, paginationUtils) {
    function getHTML() {
        if (paginationUtils.isInfiniteScroll()) {
            return '';
        }
        var response = pageStore.get(pageStore.keys.searchedResponse, null);

        var totalPages = paginationUtils.getTotalPages();
        var currentPage = paginationUtils.getCurrentPage();

        if (response === null || totalPages <= 1) {
            return '';
        }

        var pageItems = [];
        var pageIndex = 1;
        while(pageIndex <= totalPages) {
            pageItems.push({
               value: pageIndex,
               isSelected: (pageIndex == currentPage)
            });
            pageIndex++;
        }
        
        var templates = window.wizzyConfig.search.view.templates;
        var paginationTemplate = $(templates.pagination).html();
        paginationTemplate = Mustache.render(paginationTemplate, {
            items: pageItems,
            isPrevActive: (currentPage > 1),
            isNextActive: (currentPage < totalPages),
        });
        
        return paginationTemplate;
    }

    return {
        getHTML: getHTML,
    };
});