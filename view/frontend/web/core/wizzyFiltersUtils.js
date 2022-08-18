define(['jquery', 'wizzy/bundle', 'wizzy/utils/facets', 'wizzy/libs/pageStore'], function($, wizzyBundle, facetsUtils, pageStore) {
    var wizzyFiltersUtils = wizzyBundle.WizzyFilters;
    var instance = null;

    function n() {
        if (instance == null) {
            instance = (new wizzyFiltersUtils(facetsUtils.instance()));
        }
        return instance;
    }

    function setDefaultSortMethod() {
        var sortConfigs = window.wizzyConfig.search.configs.sorts.configs;
        var keys = Object.keys(sortConfigs);
        if (keys.length > 0) {
            pageStore.set(pageStore.keys.selectedSortMethod, sortConfigs[keys[0]]);
        }
    }

    return {
        new: n,
        setDefaultSortMethod: setDefaultSortMethod,
    };
});