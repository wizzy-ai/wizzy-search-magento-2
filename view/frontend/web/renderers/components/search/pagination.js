define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/utils/pagination'], function($, Mustache, pageStore, paginationUtils) {
    function getHTML() {
        if (paginationUtils.isInfiniteScroll()) {
            return '';
        }
        var response = pageStore.get(pageStore.keys.searchedResponse, null);

        var totalPages = paginationUtils.getTotalPages();
        var currentPage = paginationUtils.getCurrentPage();
        var maxConnectedPages = 3;

        if (response === null || totalPages <= 1) {
            return '';
        }

        var pageItems = [];
        var pageIndex = 1;
        if(currentPage > 1) {
            pageItems = pushPageItems(pageItems, '...', false, 'inactive eclipse');
        }
        if(totalPages <= 3) {
            while(pageIndex <= totalPages) {
                pageItems = pushPageItems(pageItems, pageIndex, (pageIndex == currentPage));
                pageIndex++;
            }
        } else {
            let count = totalPages - currentPage > 3 ? 0 : totalPages - currentPage;
            if(totalPages - currentPage > 3) {
                while(count < maxConnectedPages) {
                    pageItems = pushPageItems(pageItems, currentPage + count, (currentPage + count == currentPage));
                    count++;
                }
                if(currentPage <= totalPages - 3) {
                    pageItems = pushPageItems(pageItems, '...', false, 'inactive eclipse')
                }
                pageItems = pushPageItems(pageItems, totalPages, (totalPages == currentPage));
            } else {
                let page = currentPage
                while(page <= totalPages) {
                    pageItems = pushPageItems(pageItems, page, (page == currentPage));
                    page++;
                }
            }
        }

        var templates = window.wizzyConfig.search.view.templates;
        var paginationTemplate = $wZ(templates.pagination).html();
        paginationTemplate = Mustache.render(paginationTemplate, {
            items: pageItems,
            isPrevActive: (currentPage > 1),
            isNextActive: (currentPage < totalPages),
        });

        return paginationTemplate;
    }

    function pushPageItems(pageItems, value, isSelected, customClass = "") {
        pageItems.push({
            value: value,
            isSelected: isSelected,
            customClass:customClass
        })
        return pageItems;
    }

    return {
        getHTML: getHTML,
    };
});
