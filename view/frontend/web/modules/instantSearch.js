requirejs(['jquery', 'wizzy/libs/instantSearch', 'wizzy/utils/search', 'wizzy/libs/pageStore', 'wizzy/common'], function($, wI, searchUtils, pageStore, wizzyCommon) {
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
        $('body').on('click', '.wizzy-result-product a', function (e) {
            var parent = $(this).parents('.wizzy-result-product');
            let searchedResponse = pageStore.get("searchedResponse");
            var productId = parseInt(parent.data('id'));
            let productItem = searchedResponse.result.filter((item) => (productId === item.id));
            wizzyCommon.dataStorage.addRecentProductViewed(productItem[0]);
        });
    });
});
