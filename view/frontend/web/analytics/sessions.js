define(['jquery', 'wizzy/common'], function($, wizzyCommon) {
    function record(options) {
        var payload = {};

        $.ajax({
            url: "/search/analytics/session",
            type: "POST",
            data: JSON.stringify({
                'headers': wizzyCommon.getClient().getCommonHeaders(),
            }),
            dataType: "json",
            contentType: "application/json; charset=utf-8",
            success: function(data) {
                updateUser(data.userId);
            }
        });
    }

    function updateUser(userId) {
        if (typeof userId !== "undefined") {
            window.wizzyUserConfig.loggedInUser.id = userId;
            wizzyCommon.updateClientData();
        }
    }

    return {
        record: record,
        updateUser: updateUser,
    };
});