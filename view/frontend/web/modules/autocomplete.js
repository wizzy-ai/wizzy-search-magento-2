requirejs(['jquery', 'wizzy/libs/autocomplete'], function($, wA) {
    $(document).ready(function(e) {
        $('.wizzy-search-input').each(function(e) {
            var config = window.wizzyConfig.autocomplete.menu.view;
            config['element'] = $(this);
            config ['formSubmissionBehaviour'] = window.wizzyConfig.search.configs.general.formSubmissionBehaviour;
            wA.autocomplete(config);
        });
    });
});
