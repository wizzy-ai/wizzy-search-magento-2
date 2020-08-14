define(['jquery', 'wizzy/renderers/components/search/results'], function($, resultsComponent) {
    function showIndicator(options) {
        var groupId = options['groupId'];
        var requestId = options['requestId'];

        if($('.wizzy-group-product-' + groupId).size() > 0) {
            $('.wizzy-group-product-' + groupId).addClass('isLoading');
        }
    }

    function displayVariation(response) {
        if (typeof response.error !== "undefined" && response.error === true) {
            // Handle failed variation request.
        }
        else {
            response = response.response;
            if (response.status === 0) {
                // Handle failed variation request.
            }
            else {
                var variation = response['payload']['result'];
                if (typeof variation !== "undefined" && variation !== null) {
                    var variationHTML = resultsComponent.getProductsHTML([variation]);
                    variationHTML = variationHTML[0];
                    var groupId = (typeof variation['groupId'] !== "undefined" && variation['groupId'] !== null) ? variation['groupId'] : 0;
                    if ($('.wizzy-group-product-' + groupId).size() > 0) {
                        $('.wizzy-group-product-' + groupId).replaceWith(variationHTML['html']);
                    }
                }
            }
        }
    }

    return {
        showIndicator: showIndicator,
        displayVariation: displayVariation,
    };
});