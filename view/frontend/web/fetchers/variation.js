define(['jquery', 'wizzy/common', 'wizzy/libs/pageStore', 'wizzy/renderers/variation', 'wizzy/utils/search'], function($, wizzyCommon, pageStore, variationRenderer, searchUtils) {
    function execute(options) {
        var variationId = typeof options['variationId'] === "undefined" ? '' : options['variationId'];
        var groupId = typeof options['groupId'] === "undefined" ? '' : options['groupId'];

        var payload = {
            groupId: groupId,
            variationId: variationId,
            swatch: searchUtils.getSwatchesToAdd(),
            currency: window.wizzyConfig.store.currency.code,
        };

        if (variationId !== "" && groupId !== "") {
            var response = wizzyCommon.getClient().variation(payload);
            variationRenderer.showIndicator({
                groupId: groupId,
                requestId: response.requestId
            });
        }
    }

    return {
        execute: execute,
    };
});