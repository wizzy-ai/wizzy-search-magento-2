var wizzy = {
    allowedEvents: {
        AFTER_PRODUCTS_TRANSFORMED: 'afterProductsTransformed',
        BEFORE_SEARCH_EXECUTED: 'beforeSearchExecuted',
        BEFORE_FILTERS_EXECUTED: 'beforeFiltersExecuted',
        PRODUCT_SWATCH_CLICKED: 'productSwatchClicked',
        VIEW_RENDERED: 'viewRendered',
        PRODUCTS_RESULTS_RENDERED: 'productsResultsRendered',
        BEFORE_PRODUCTS_RESULTS_RENDERED: 'beforeProductsResultsRendered',
        EMPTY_RESULTS_RENDERED: 'emptyResultsRendered',
        BEFORE_AUTOCOMPLETE_EXECUTED: 'beforeAutocompleteExecuted',
        AFTER_FILTER_ITEM_CLICKED: 'afterFilterItemClicked',
        BEFORE_SORT_EXECUTED: 'beforeSortExecuted',
        BEFORE_INIT: 'beforeInit',
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
        window.wizzyConfig = wizzy.triggerEvent(wizzy.allowedEvents.BEFORE_INIT, window.wizzyConfig);
    });
});
