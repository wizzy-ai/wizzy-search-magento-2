define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/utils/url'], function($, Mustache, pageStore, commonUrlUtils) {

    function getDOMHandler() {
        return window.wizzyConfig.search.view.domSelector;
    }

    function revertDOM() {
        var beforeSearchDOM = pageStore.get(pageStore.keys.beforeSearchDOM);
        if (beforeSearchDOM !== null && !commonUrlUtils.hasSearchEndPointInUrl(beforeSearchDOM.url)) {
            $(getDOMHandler()).html(beforeSearchDOM.html);
        }
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
        updateResultsDOM: updateResultsDOM,
        appendDOM: appendDOM,
    };
});