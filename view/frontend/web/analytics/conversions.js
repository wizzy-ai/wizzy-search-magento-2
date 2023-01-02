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
            $.ajax({
                url: window.wizzyConfig.analytics.endpoints.conversions,
                type: 'POST',
                data: JSON.stringify({
                    'type': 'conversion',
                    'headers': wizzyCommon.getClient().getCommonHeaders(),
                    'data': payload
                }),
                dataType: 'application/json, text/plain, */*',
                ContentType: 'application/json; charset=utf-8',
                success: function (data) {
                    aS.updateUser(data.userId);
                }
            });
        }
    }

    return {
        record: record,
    };
});
