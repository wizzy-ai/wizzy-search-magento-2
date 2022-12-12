define(['wizzy/libs/pageStore', 'underscore', 'wizzy/renderers/components/filters/swatch', 'wizzy/utils/cookie'], function(pageStore, _, swatchComponent, cUtils) {

    Number.prototype.formatMoney = function(decPlaces, thouSeparator, decSeparator) {
        var n = this,
            decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
            decSeparator = decSeparator == undefined ? "." : decSeparator,
            thouSeparator = thouSeparator == undefined ? "," : thouSeparator,
            i = parseInt(n = Math.abs(+n || 0).toFixed(decPlaces)) + "",
            j = (j = i.length) > 3 ? j % 3 : 0;
        return (j ? i.substr(0, j) + thouSeparator : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thouSeparator) + (decPlaces ? decSeparator + Math.abs(n - i).toFixed(decPlaces).slice(2) : "");
    };

    function getTransformedProducts(products) {
        var totalProducts = products.length;
        resetFormKey();
        for (var i = 0; i < totalProducts; i++) {
            var categories = products[i]['categories'];
            if (typeof categories !== "undefined" && categories.length > 0) {
                categories = _.sortBy(categories, function(category) {
                    return category['level'];
                }, 'desc');
                categories = categories.reverse();
                products[i]['subTitle'] = categories[0]['name'];
            }

            products[i] = setNullIfZero([
                'price',
                'finalPrice',
                'avgRatings',
                'totalReviews',
            ], products[i]);

            if (typeof products[i].avgRatings !== "undefined") {
                products[i].avgRatings = Math.round((products[i].avgRatings / 20)*2)/2;
            }

            products[i] = appendModifiedSwatches(products[i]);
            products[i] = appendCartValues(products[i]);
            products[i] = formatPrices(products[i]);
            products[i] = addTemplateAttrbutes(products[i]);
        }
        products = wizzy.triggerEvent(wizzy.allowedEvents.AFTER_PRODUCTS_TRANSFORMED, products);
        return products;
    }

    function addTemplateAttrbutes(product)
    {
        var templateAttributes = wizzyConfig.common.templateAttributes;
        if (templateAttributes.length === 0) {
            return product;
        }

        if (typeof product.attributes !== "undefined" && Array.isArray(product.attributes)) {
            var totalAttributes = product.attributes.length;

            for (var j = 0; j < totalAttributes; j++) {
                var attributeName = product.attributes[j].id;

                if (templateAttributes.includes(attributeName)) {
                    if (typeof product.attributes[j].values !== "undefined" && product.attributes[j].values.length > 0) {

                        var attributeValues = product.attributes[j].values;
                        var totalValues = attributeValues.length;

                        if (totalValues > 0) {
                            product[attributeName] = [];
                        }

                        for (var k = 0; k < totalValues; k++) {
                            if (typeof product.attributes[j].values[k].value !== "undefined" && product.attributes[j].values[k].variationId == product.id && product.attributes[j].values[k].value.length > 0) {
                                product[attributeName].push(product.attributes[j].values[k].value[0]);
                            }
                        }
                    }
                }
            }
        }

        return product;
    }

    function resetFormKey() {
        var cookieFormKey = cUtils.getCookie('form_key');

        if(cookieFormKey != "" && window.wizzyConfig.search.addToCart.formKey != cookieFormKey) {
            window.wizzyConfig.search.addToCart.formKey = cookieFormKey;
        }
    }

    function setNullIfZero(fields, product) {
        var totalFields = fields.length;

        for(var j = 0; j < totalFields; j++) {
            if (typeof product[fields[j]] !== "undefined" && product[fields[j]] == 0) {
                product[fields[j]] = null;
            }
        }

        return product;
    }

    function formatPrices(product) {
        let priceKeys = [
            'price',
            'finalPrice',
            'sellingPrice',
        ];

        var totalKeys = priceKeys.length;

        for(var j = 0; j < totalKeys; j++) {
            if (typeof product[priceKeys[j]] !== "undefined" && product[priceKeys[j]] !== null) {
                var fieldVale = parseFloat(product[priceKeys[j]]);
                product[priceKeys[j]] = Number(fieldVale.toFixed(2)).formatMoney();
            }
        }

        return product;
    }

    function appendCartValues(product) {
        var cartAction = window.wizzyConfig.search.addToCart.formAction + 'product/' + product.id + '/';
        var cartFormKey = window.wizzyConfig.search.addToCart.formKey;

        product['cart'] = {
            'action': cartAction,
            'uenc': window.btoa(cartAction),
            'formKey': cartFormKey,
            'searchResponseId': pageStore.get(pageStore.keys.lastResponseId),
        };

        return product
    }

    function appendModifiedSwatches(product) {
        if (typeof product['swatches'] !== "undefined") {
            var swatchesKeys = Object.keys(product['swatches']);
            if (swatchesKeys.length > 0) {
                product['swatches'] = getSwatchesToDisplay(product['id'], product['swatches']);
                if (product['swatches'].length > 0) {
                    product['hasSwatches'] = true;
                }
            }
        }

        return product;
    }

    function getSwatchesToDisplay(variantId, productSwatches) {
        var sortedSwatches = _.sortBy(productSwatches, function(swatch) {
            return swatch['order'];
        });
        var modifiedSwatches = getModifiedSwatches(productSwatches);
        var keyValueVariationSwatches = modifiedSwatches['keyValueVariation'];
        var variationKeyValueSwatches = modifiedSwatches['variationKeyValue'];
        var primarySwatch = sortedSwatches[0].key;

        var swatchesToDisplay = [];
        var primarySwatchesToDisplay = getSwatchesByKey(primarySwatch, keyValueVariationSwatches, variantId);
        if (primarySwatchesToDisplay.length > 0) {
            swatchesToDisplay.push({
                isPrimary: true,
                key: primarySwatch,
                values:primarySwatchesToDisplay,
            });
        }

        var totalSwatches = sortedSwatches.length;
        var dependentSwatches = [primarySwatch];

        for (var i = 1; i < totalSwatches; i++) {
            var swatchValues = getSubSwatches(dependentSwatches, sortedSwatches[i].key, keyValueVariationSwatches,  variationKeyValueSwatches, variantId);
            if (swatchValues.length > 0) {
                swatchesToDisplay.push({
                    isPrimary: false,
                    key: sortedSwatches[i].key,
                    values: swatchValues,
                });
            }

            dependentSwatches.push(sortedSwatches[i].key);
        }

        totalSwatches = swatchesToDisplay.length;
        for(i = 0; i < totalSwatches; i++) {
            swatchesToDisplay[i].values = swatchComponent.getData(swatchesToDisplay[i].values);
        }

        return swatchesToDisplay;
    }

    function getSubSwatches(dependentSwatches, swatchToAdd, keyValueVariationSwatches,  variationKeyValueSwatches, variantId) {
        var totalDependentSwatches = dependentSwatches.length;
        if (typeof variationKeyValueSwatches[variantId] === "undefined") {
            return [];
        }
        var variationIdsToCheck = null;
        var swatchesToReturn = {};

        for (var j = 0; j < totalDependentSwatches; j++) {
            var dependentSwatch = dependentSwatches[j];
            if (typeof variationKeyValueSwatches[variantId][dependentSwatch] === "undefined" || typeof keyValueVariationSwatches[dependentSwatch] === "undefined") {
                continue;
            }

            var dependentValues = Object.keys(variationKeyValueSwatches[variantId][dependentSwatch]);
            var totalDependentValues = dependentValues.length;

            for (var k = 0; k < totalDependentValues; k++) {
                if (typeof keyValueVariationSwatches[dependentSwatch][dependentValues[k]] !== "undefined") {
                    var variationIds = Object.keys(keyValueVariationSwatches[dependentSwatch][dependentValues[k]]);
                    if (variationIdsToCheck === null) {
                        variationIdsToCheck = variationIds;
                    }
                    else {
                        variationIdsToCheck = variationIdsToCheck.filter(value => variationIds.includes(value))
                    }
                }
            }
        }

        if (variationIdsToCheck == null) {
            return [];
        }

        var totalVariationsToCheck = variationIdsToCheck.length;
        for (var i = 0; i < totalVariationsToCheck; i++) {
            var variationIdToCheck = variationIdsToCheck[i];
            if (typeof variationKeyValueSwatches[variationIdToCheck] !== "undefined" && typeof variationKeyValueSwatches[variationIdToCheck][swatchToAdd] !== "undefined") {
                var swatchKeys = Object.keys(variationKeyValueSwatches[variationIdToCheck][swatchToAdd]);
                var totalSwatchKeys = swatchKeys.length;
                for (j = 0; j < totalSwatchKeys; j++) {
                    var swatchObject = variationKeyValueSwatches[variationIdToCheck][swatchToAdd][swatchKeys[j]];
                    var isSelected = false;
                    if (typeof keyValueVariationSwatches[swatchToAdd][swatchKeys[j]][variantId] !== "undefined") {
                        isSelected = true;
                    }
                    swatchesToReturn[swatchKeys[j]] = getModifiedSwatchObject(swatchObject, swatchKeys[j], isSelected);
                }
            }
        }

        return Object.values(swatchesToReturn);
    }

    function getModifiedSwatchObject(swatchObject, value, isSelected) {
        swatchObject['value'] = value;
        swatchObject['isSelected'] = isSelected;
        swatchObject['variationId'] = swatchObject['variationId'];

        if (typeof swatchObject['swatch'] !== "undefined") {
            swatchObject['data'] = {};
            swatchObject['data']['swatch'] = swatchObject['swatch'];
        }

        return swatchObject;
    }

    function getSwatchesByKey(key, keyValueVariationSwatches, variantId) {
        if (typeof keyValueVariationSwatches[key] === "undefined") {
            return [];
        }

        var values = Object.keys(keyValueVariationSwatches[key]);
        var totalValues = values.length;

        var swatches = [];

        for (var i = 0; i < totalValues; i++) {
            var swatchObject;
            var isSelected = false;

            if (typeof keyValueVariationSwatches[key][values[i]][variantId] !== "undefined") {
                swatchObject = keyValueVariationSwatches[key][values[i]][variantId];
                isSelected = true;
            }
            else {
                var variantKeys = Object.keys(keyValueVariationSwatches[key][values[i]]);
                if (variantKeys.length === 0) {
                    continue;
                }
                swatchObject = keyValueVariationSwatches[key][values[i]][variantKeys[0]];
            }
            swatches.push(getModifiedSwatchObject(swatchObject, values[i], isSelected));
        }

        return swatches;
    }

    function getModifiedSwatches(productSwatches) {
        var swatchKeys = Object.keys(productSwatches);
        var totalProductSwatches = swatchKeys.length;
        var keyValueVariationSwatches = {};
        var variationKeyValueSwatches = {};

        for (var i = 0; i < totalProductSwatches; i++) {
            var swatchKey = swatchKeys[i];
            if (typeof keyValueVariationSwatches[swatchKey] === "undefined") {
                keyValueVariationSwatches[swatchKey] = {};
            }
            var swatchValues = productSwatches[swatchKey]['values'];
            var totalSwatchValues = swatchValues.length;

            for (var j = 0; j < totalSwatchValues; j++) {
                var swatchValue = swatchValues[j];
                var variationId = swatchValue['variationId'];

                if (typeof variationKeyValueSwatches[variationId] === "undefined") {
                    variationKeyValueSwatches[variationId] = {};
                }

                if (typeof variationKeyValueSwatches[variationId][swatchKey] === "undefined") {
                    variationKeyValueSwatches[variationId][swatchKey] = {};
                }

                var swatchIndividualValues = swatchValue['value'];
                if (typeof swatchIndividualValues === "string" || typeof swatchIndividualValues !== "object") {
                    swatchIndividualValues = [swatchIndividualValues];
                }
                var totalIndividualValues = swatchIndividualValues.length;

                for(var k = 0; k < totalIndividualValues; k++) {
                    var individualValue = swatchIndividualValues[k];

                    if (typeof keyValueVariationSwatches[swatchKey][individualValue] === "undefined") {
                        keyValueVariationSwatches[swatchKey][individualValue] = {};
                    }

                    keyValueVariationSwatches[swatchKey][individualValue][variationId] = swatchValue;
                    variationKeyValueSwatches[variationId][swatchKey][individualValue] = swatchValue;
                }
            }
        }

        return {
            keyValueVariation: keyValueVariationSwatches,
            variationKeyValue: variationKeyValueSwatches
        };
    }

    return {
        getTransformedProducts: getTransformedProducts,
    };
});
