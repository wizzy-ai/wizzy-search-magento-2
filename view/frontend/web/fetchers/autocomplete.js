define(['jquery', 'wizzy/common', 'wizzy/libs/pageStore'], function($, wizzyCommon, pageStore) {
    return function(options) {
        var q = typeof options['q'] === "undefined" ? "" : options['q'];
        var element = typeof options['element'] === "undefined" ? null : options['element'];

        if (element) {
            var payload = {
                q: q,
                currency: window.wizzyConfig.store.currency.code,
                suggestionsCount: window.wizzyConfig.autocomplete.menu.suggestionsCount,
            };
            if (window.wizzyConfig.autocomplete.topProducts.suggestTopProduts && window.wizzyConfig.autocomplete.topProducts.count > 0) {
                payload['productsCount'] = window.wizzyConfig.autocomplete.topProducts.count;
            }
            var response = wizzyCommon.getClient().autocomplete(payload);
            pageStore.set(pageStore.keys.lastRequestIdAutocomplete, response.requestId);
        }
    };
});