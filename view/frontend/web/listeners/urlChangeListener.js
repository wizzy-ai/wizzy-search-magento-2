define(['wizzy/libs/pageStore', 'wizzy/renderers/search'], function(pageStore, searchRenderer) {

    function onChange(event) {
        var beforeSearchDOM = pageStore.get(pageStore.keys.beforeSearchDOM);
        if ((!event.state || (typeof event.state.url !== "undefined" && event.state.url === beforeSearchDOM.url)) && beforeSearchDOM.url === window.location.href) {
            searchRenderer.revertDOM();
        }
    }

    return {
        onChange: onChange,
    };
});