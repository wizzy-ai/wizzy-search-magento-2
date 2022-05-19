requirejs(['jquery', 'wizzy/libs/instantSearch', 'wizzy/utils/search'], function($, wI, searchUtils) {
    $(document).ready(function(e) {
        wI.init();
        $(searchUtils.getInputDOM()).each(function(e) {
            var config = {};
            config['element'] = $(this);

            if (!window.wizzyConfig.autocomplete.enabled && window.wizzyConfig.search.configs.general.behaviour === "search_as_you_type") {
                config['behavior'] = "ontype";
            }
            else {
                config['behavior'] = "onenter";
            }

            config ['formSubmissionBehaviour'] = window.wizzyConfig.search.configs.general.formSubmissionBehaviour;
            wI.search(config);
        });
    });
});
