var wizzy = {
    allowedEvents: {
        AFTER_PRODUCTS_TRANSFORMED: 'afterProductsTransformed'
    },
    registeredEvents: [],
    registerEvent: function (eventName, callback) {
        if (typeof this.allowedEvents.AFTER_PRODUCTS_TRANSFORMED === "undefined") {
            return;
        }

        if (!this.registeredEvents[eventName]) {
            this.registeredEvents[eventName] = [callback];
        } else {
            this.registeredEvents[eventName].push(callback);
        }
    },
    getRegisteredEvents: function(eventName) {
        if (typeof this.allowedEvents.AFTER_PRODUCTS_TRANSFORMED === "undefined" || typeof this.registeredEvents[eventName] === "undefined") {
            return [];
        }

        return this.registeredEvents[eventName];
    },
    triggerEvent: function () {
        var eventName = arguments[0],
            mainData = arguments[1],
            eventArguments = Array.prototype.slice.call(arguments, 2);

        var data = this.getRegisteredEvents(eventName).reduce(function(eventData, event) {
            if (Array.isArray(eventData)) {
                eventData = [eventData];
            }
            var allParameters = [].concat(eventData).concat(eventArguments);
            return event.apply(null, allParameters);
        }, mainData);

        return data;
    }
};

requirejs(['jquery', 'wizzy/common'], function($, wizzyCommon) {
    $(document).ready(function(e) {
        wizzyCommon.connect();
    });
});