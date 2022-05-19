requirejs(['jquery', 'wizzy/libs/autocomplete', 'wizzy/utils/search'], function($, wA, searchUtils) {
    $(document).ready(function(e) {
        $(searchUtils.getInputDOM()).each(function(e) {
            var config = window.wizzyConfig.autocomplete.menu.view;
            config['element'] = $(this);
            config ['formSubmissionBehaviour'] = window.wizzyConfig.search.configs.general.formSubmissionBehaviour;
            wA.autocomplete(config);
        });
    });
});
