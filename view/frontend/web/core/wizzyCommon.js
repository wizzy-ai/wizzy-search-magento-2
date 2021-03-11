define(['jquery', 'wizzy/bundle', 'wizzy/renderers/wrapper'], function($, wizzyBundle, wizzyRenderer) {
    var connectionTries = 0;
    var maxConnectionTries = 300;
    var wizzyClient;
    var isOpened = false;
    var wizzyUtils = wizzyBundle.WizzyUtils;

    function addSocketListeners() {
        wizzyClient
            .onClose(function (event) {
                isOpened = false;
                if (connectionTries < maxConnectionTries) {
                    setTimeout(function () {
                        connect();
                    }, 3000);
                }
            })
            .onOpen(function (event) {
                connectionTries = 0;
                isOpened = true;
            })
            .onMessage(function (data) {
                wizzyRenderer(JSON.parse(data.data));
            })
            .onError(function (event) {

            });
    }

    function connect() {
        connectionTries++;
        wizzyClient = new wizzyBundle.WizzyClient(window.wizzyConfig.credentials.apiKey, window.wizzyConfig.credentials.storeId, {
            "USER_ID": window.wizzyUserConfig.loggedInUser.id,
        });
        wizzyClient.connect();
        addSocketListeners();
    }

    function getClient() {
        return wizzyClient;
    }

    function isConnected() {
        return isOpened;
    }

    return {
        connect: connect,
        getClient: getClient,
        isConnected: isConnected,
        utils: (new wizzyUtils()),
    };
});