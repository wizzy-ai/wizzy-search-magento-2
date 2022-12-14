define(['jquery', 'wizzy/common', 'wizzy/analytics/sessions'], function ($, wizzyCommon, aS) {
    function record(options) {
        var items = typeof options['items'] === "undefined" ? '' : options['items'];
        var searchResponseId = typeof options['responseId'] === "undefined" ? '' : options['responseId'];
        var name = typeof options['name'] === "undefined" ? '' : options['name'];

        var payload = {
            'name': name,
            'searchResponseId': searchResponseId,
            'items': items,
        };

        if (typeof window.wizzyConfig.analytics.endpoints.conversions !== "undefined" && window.wizzyConfig.analytics.endpoints.conversions) {
            fetch(window.wizzyConfig.analytics.endpoints.conversions, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json, text/plain, */*',
                    'Content-Type': 'application/json; charset=utf-8'
                },
                body: JSON.stringify({
                    'type': 'conversion',
                    'headers': wizzyCommon.getClient().getCommonHeaders(),
                    'data': payload
                })
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                aS.updateUser(data.userId);
            });
        }
    }

    return {
        record: record,
    };
});
