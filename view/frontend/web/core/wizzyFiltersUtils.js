define(['jquery', 'wizzy/bundle', 'wizzy/utils/facets'], function($, wizzyBundle, facetsUtils) {
    var wizzyFiltersUtils = wizzyBundle.WizzyFilters;
    return (new wizzyFiltersUtils(facetsUtils.instance()));
});