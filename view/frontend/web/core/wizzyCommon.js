define(['jquery', 'wizzy/bundle', 'wizzy/renderers/wrapper'], function($, wizzyBundle, wizzyRenderer) {
    var connectionTries = 0;
    var maxConnectionTries = 50;
    var wizzyClient;
    var isOpened = false;
    var wizzyUtils = wizzyBundle.WizzyUtils;
    var wizzyDataStorage = wizzyBundle.WizzyDataStorage;
    var wizzySessionDataStorage = wizzyBundle.WizzySessionDataStorage;
    var wizzyEvents = wizzyBundle.WizzyEvents;

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
                wizzyRenderer(JSON.parse(data.data), {
                    sessionDataStorage: (wizzySessionDataStorage.singleton()),
                });
            })
            .onError(function (event) {

            });
    }

    function connect() {
        connectionTries++;
        wizzyClient = new wizzyBundle.WizzyClient(window.wizzyConfig.credentials.apiKey, window.wizzyConfig.credentials.storeId, getClientData());
        wizzyClient.connect();
        addSocketListeners();
    }

    function updateClientData() {
        wizzyClient.updateData(getClientData());
    }

    function getClientData() {
        return {
            "USER_ID": window.wizzyUserConfig.loggedInUser.id,
        };
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
        events: wizzyEvents,
        updateClientData: updateClientData,
        dataStorage: (wizzyDataStorage.singleton()),
        sessionDataStorage: (wizzySessionDataStorage.singleton()),
    };
});