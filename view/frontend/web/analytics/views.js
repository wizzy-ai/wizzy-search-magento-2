define(['jquery', 'wizzy/common', 'wizzy/analytics/sessions'], function($, wizzyCommon, aS) {
    function record(options) {
        var items = typeof options['items'] === "undefined" ? '' : options['items'];
        var searchResponseId = typeof options['responseId'] === "undefined" ? '' : options['responseId'];
        var name = typeof options['name'] === "undefined" ? '' : options['name'];

        var payload = {
            'name' : name,
            'searchResponseId' : searchResponseId,
            'items': items,
        };

        $.ajax({
            url: "/search/analytics/collect",
            type: "POST",
            data: JSON.stringify({
                'type' : 'view',
                'headers': wizzyCommon.getClient().getCommonHeaders(),
                'data' : payload
            }),
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            success: function(data) {
                aS.updateUser(data.userId);
            }
        });
    }

    return {
        record: record,
    };
});