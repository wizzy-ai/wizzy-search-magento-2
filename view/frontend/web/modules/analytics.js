requirejs(['jquery', 'wizzy/common', 'wizzy/analytics/clicks', 'wizzy/libs/pageStore', 'wizzy/analytics/views', 'wizzy/analytics/sessions'], function($, wC, aC, pS, aV, aS) {
    $(document).ready(function(e) {
        $('body').on('click', '.wizzy-result-product a', function(e) {
            var parent = $(this).parents('.wizzy-result-product');
            addClickInStorage(parent);
        });

        $('body').on('click', '.topproduct-item a', function(e) {
            var parent = $(this).parents('.topproduct-item');
            addClickInStorage(parent);
        });

        recordProductViewAndClick();
        recordSession();
    });

    function recordSession() {
        var hasSessionQueue = getCookie('WIZZY_SESSION_QUEUE');
        if (hasSessionQueue) {
            aS.record({});
        }
    }

    function getCookie(name) {
        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
        return match ? match[1] : null;
    }

    function addClickInStorage(parent) {
        var productId = parent.data('id');
        var productGroupId = parent.data('groupid');

        if (typeof productGroupId !== "undefined" && productGroupId !== "" && productGroupId !== null) {
            productId = productGroupId;
        }

        wC.dataStorage.addClickedProduct(productId, pS.get(pS.keys.lastResponseId), (parent.index() + 1));
    }

    function recordProductViewAndClick() {
        if (window.wizzyConfig.common.isOnProductViewPage && typeof window.wizzyConfig.common.currentProductId !== "undefined") {
            var currentProductId = window.wizzyConfig.common.currentProductId;
            var clickDetails = wC.dataStorage.getClickedProductDetails(currentProductId);
            var viewPayload = {
                items: [
                    {
                        itemId: window.wizzyConfig.common.currentProductId,
                    }
                ],
                name: wC.events.NAMES[wC.events.PRODUCT_VIEWED],
            };

            if (clickDetails !== null) {
                aC.record({
                    items: [
                        {
                            itemId: clickDetails['productId'] + "",
                            position: clickDetails['position'],
                        }
                    ],
                    responseId: clickDetails['responseId'],
                    name: wC.events.NAMES[wC.events.SEARCH_RESULTS_CLICKED],
                });
                viewPayload['responseId'] = clickDetails['responseId'];
                aV.record(viewPayload);
            }

            wC.dataStorage.removeClickedProduct(currentProductId);
        }
    }
});
