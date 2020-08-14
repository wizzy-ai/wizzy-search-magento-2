define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/renderers/components/filters/facetBlock', 'underscore'], function($, Mustache, pageStore, facetBlock, _) {

    function getLeftFacets() {
        if (!hasFacets()) {
            return [];
        }
        var response = getSearchedResponse();
        return getFacetsHTMLByType(response.facets, 'left');
    }

    function getSearchedResponse() {
        return pageStore.get(pageStore.keys.searchedResponse, null);
    }

    function getFacetsHTMLByType(facets, type) {
        var facetsList = getFacetsList(facets, type);
        var totalFacetsToDisplay = facetsList.length;
        var facetsHTML = [];
        if (totalFacetsToDisplay > 0) {
            for (var i = 0; i < totalFacetsToDisplay; i++) {
                var html = facetBlock.getHTML(facetsList[i], type);
                if (html !== null) {
                    facetsHTML.push({
                        'html': html
                    });
                }
            }
        }

        return facetsHTML;
    }

    function getTopFacets() {
        if (!hasFacets()) {
            return [];
        }
        var response = getSearchedResponse();
        return getFacetsHTMLByType(response.facets, 'top');
    }

    function hasFacets() {
        var response = getSearchedResponse();
        if (response === null || typeof response.facets === "undefined") {
            return false;
        }

        return true;
    }

    function getFacetsList(facets, type = "left") {
        var totalFacets = facets.length;
        var facetsList = [];
        for (var i = 0; i < totalFacets; i++) {
            if (facets[i].position === type) {
                facetsList.push(facets[i]);
            }
        }

        return _.sortBy(facetsList, function(facet) {
            return facet['order'];
        });
    }

    function getAllFacets() {
        if (!hasFacets()) {
            return [];
        }
        var response = getSearchedResponse();
        return response.facets;
    }

    function moveTopFacetLeft() {
        $('.wizzy-search-filters-left-mobile-extra').html($('.wizzy-search-filters-list-top').html());
    }

    return {
        getAllFacets: getAllFacets,
        getLeftFacets: getLeftFacets,
        getTopFacets: getTopFacets,
        moveTopFacetLeft: moveTopFacetLeft,
    };
});