define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/utils/url'], function($, Mustache, pageStore, commonUrlUtils) {

    function getDOMHandler() {
        return window.wizzyConfig.search.view.domSelector;
    }

    function getNoProductsFoundDOM() {
        return window.wizzyConfig.search.view.noProductsFoundDOM;
    }

    function revertDOM() {
        var beforeSearchDOM = pageStore.get(pageStore.keys.beforeSearchDOM);
        if (beforeSearchDOM !== null && !commonUrlUtils.hasSearchEndPointInUrl(beforeSearchDOM.url)) {
            $(getDOMHandler()).html(beforeSearchDOM.html);

            $('.page-title-wrapper').show();
            $('.breadcrumbs').show();
            $('.category-view').show();
        }
    }

    function removeUnnecessaryBlocks() {
        $('.page-title-wrapper').hide();
        $('.breadcrumbs').hide();
        $('.category-view').hide();
    }

    function setBeforeSearchDOM() {
        var beforeSearchDOM = pageStore.get(pageStore.keys.beforeSearchDOM);
        if (beforeSearchDOM === "") {
            pageStore.set(pageStore.keys.beforeSearchDOM, {
                'html' : $(getDOMHandler()).html(),
                'title': $(document).find("title").text(),
                'url' : window.location.href,
            });
        }
    }

    function updateResultsDOM(html) {
        $(getDOMHandler()).html(html);
    }

    function appendDOM(html) {
        $(getDOMHandler()).append(html);
    }

    return {
        revertDOM: revertDOM,
        setBeforeSearchDOM: setBeforeSearchDOM,
        getDOMHandler: getDOMHandler,
        removeUnnecessaryBlocks: removeUnnecessaryBlocks,
        updateResultsDOM: updateResultsDOM,
        appendDOM: appendDOM,
        getNoProductsFoundDOM:getNoProductsFoundDOM
    };
});