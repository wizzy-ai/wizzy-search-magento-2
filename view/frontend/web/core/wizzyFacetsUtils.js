define(['jquery', 'wizzy/bundle'], function($, wizzyBundle) {
    var facets = null;

    function getInstance() {
        if (facets === null) {
            var wizzyFacetsUtils = wizzyBundle.WizzyFacets;
            facets = new wizzyFacetsUtils();
        }

        return facets;
    }
    
    return {
        instance: getInstance
    };
});