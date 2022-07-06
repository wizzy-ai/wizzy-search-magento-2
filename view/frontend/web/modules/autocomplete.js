requirejs(['jquery', 'wizzy/libs/autocomplete', 'wizzy/utils/search'], function($, wA, searchUtils) {
    var fetchedDefaultSetOfProducts = false;
    $(document).ready(function(e) {
        fetchDefaultSetOfProducts();
        $(searchUtils.getInputDOM()).each(function(e) {
            var config = window.wizzyConfig.autocomplete.menu.view;
            config['element'] = $(this);
            config ['formSubmissionBehaviour'] = window.wizzyConfig.search.configs.general.formSubmissionBehaviour;
            wA.autocomplete(config);
        });
    });
    function fetchDefaultSetOfProducts() {
        if (fetchedDefaultSetOfProducts === false) {
            fetchedDefaultSetOfProducts = true;
            wA.fetchDefaultSetOfProducts();
        }
    }
});
