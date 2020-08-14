define([], function() {
    function getData(data) {
        var totalData = data.length;
        if (totalData === 0) {
            return [];
        }

        for (var i = 0; i < totalData ; i++) {
            var isSwatch = false;
            var swatchData = getSwatchData(data[i]);

            data[i]["isSwatch"] = swatchData['isSwatch'];
            data[i]["isVisualSwatch"] = swatchData['isVisualSwatch'];
            data[i]["isURLSwatch"] = swatchData['isURLSwatch'];
            data[i]["swatchValue"] = swatchData['swatchValue'];
        }

        return data;
    }

    function getSwatchData(data) {
        var isSwatch = false;
        var isVisualSwatch = false;
        var isURLSwatch = false;
        var swatchValue = '';

        if (typeof data['data'] !== "undefined" && typeof data["data"]["swatch"] !== "undefined") {
            isSwatch = true;
            swatchValue = data["data"]["swatch"]["value"];

            if (data["data"]["swatch"]["type"] === "visual") {
                isVisualSwatch = true;
                if (isValidURL(swatchValue)) {
                    isURLSwatch = true;
                }
            }
        }

        return {
            isSwatch,
            isVisualSwatch,
            isURLSwatch,
            swatchValue,
        }
    }

    function isValidURL(value) {
        var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
            '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|'+ // domain name
            '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
            '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
            '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
            '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
        return !!pattern.test(value);
    }

    return {
        getData: getData,
        getSwatchData: getSwatchData,
    };
});