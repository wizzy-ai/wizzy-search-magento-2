define(['jquery', 'Mustache', 'wizzy/libs/pageStore', 'wizzy/renderers/components/search/summary', 'wizzy/renderers/components/search/sort','wizzy/renderers/components/search/results', 'wizzy/renderers/components/filters/facets', 'wizzy/renderers/components/filters/range', 'wizzy/renderers/dom', 'wizzy/utils/facets', 'wizzy/utils/filters', 'wizzy/renderers/components/filters/selectedFacets', 'wizzy/renderers/components/search/pagination', 'wizzy/renderers/components/search/select'], function($, Mustache, pageStore, summaryComponent, sortComponent, resultsComponent, facetsComponent, rangeComponent, domRenderer, facetsUtils, filtersUtils, selectedFacetsComponent, paginationComponent, selectComponent) {

    var wizzyCommonConfig = null;
    var wizzySearchConfig = null;


    $(document).ready(function(e) {
        setOnLoadConfigs();
    });

    function setOnLoadConfigs() {
        setSortMethod();
    }

    function setSortMethod() {
        var sortConfigs = getSearchConfig().configs.sorts.configs;
        var keys = Object.keys(sortConfigs);
        if (keys.length > 0) {
            pageStore.set(pageStore.keys.selectedSortMethod, sortConfigs[keys[0]]);
        }
    }

    function getCommonConfig() {
        if (wizzyCommonConfig == null) {
            wizzyCommonConfig = window.wizzyConfig.common.view;
        }

        return wizzyCommonConfig;
    }

    function getSearchConfig() {
        if (wizzySearchConfig == null) {
            wizzySearchConfig = window.wizzyConfig.search;
        }

        return wizzySearchConfig;
    }

    function revertDOM() {
        domRenderer.revertDOM();
    }

    function showIndicator(isForFilter, removeBlocks) {
        var searchedResponse = pageStore.get(pageStore.keys.searchedResponse, null);
        var isPaginating = pageStore.get(pageStore.keys.isPaginating, false);
        var isCategoryPageRendered = pageStore.get(pageStore.keys.isCategoryPageRendered, false);
        var displayCategoryLoader = ((isOnCategoryPage() && isCategoryPageRendered) || !isOnCategoryPage());

        domRenderer.setBeforeSearchDOM();
        if (removeBlocks) {
            domRenderer.removeUnnecessaryBlocks();
        }
        var isBlankSearchResponse = (searchedResponse === "" || searchedResponse === null || searchedResponse.length === 0);

        if (isBlankSearchResponse || isForFilter) {
            var progressTemplate = getProgressTemplate((isForFilter && displayCategoryLoader), isPaginating);

            if (isForFilter && displayCategoryLoader) {
                if (!(isBlankSearchResponse)) {
                    if (isPaginating) {
                        $('.wizzy-search-pagination').append(progressTemplate);
                    }
                    else {
                        domRenderer.appendDOM(progressTemplate);
                    }
                }
            }
            else {
                domRenderer.updateResultsDOM(progressTemplate);
            }
        }
    }

    function showIndicatorOnPageLoad() {
        var progressTemplate = getProgressTemplate(false, false);
        domRenderer.updateResultsDOM(progressTemplate);
    }

    function getProgressTemplate(isForFilter, isPaginating) {
        var progressTemplate = $(getCommonConfig().templates.progress).html();
        progressTemplate = Mustache.render(progressTemplate, {
            isForFilter: isForFilter,
            isPaginating: isPaginating,
        });
        return progressTemplate;
    }

    function showLoaderForSpecificDOM(domHandler) {
        var progressTemplate = $(getCommonConfig().templates.progress).html();
        $(domHandler).html(progressTemplate);
    }

    function displayResults(response) {
        response = wizzy.triggerEvent(wizzy.allowedEvents.BEFORE_RENDER_RESULTS, response);
        if (typeof response.error !== "undefined" && response.error === true) {
            handleFailedSearch();
        }
        else {
            response = response.response;
            if (response.status === 0) {
                handleFailedSearch();
            }
            else {
                pageStore.set(pageStore.keys.searchedResponse, response.payload);
                refreshSearchSelections();
                renderResults();
            }
        }
    }

    function refreshSearchSelections() {
        pageStore.set(pageStore.keys.selectedFilters, []);
    }

    function renderResults() {
        if (!resultsComponent.hasProducts()) {
            handleFailedSearch();
            return;
        }

        var templates = getSearchConfig().view.templates;
        var wrapperTemplate = $(templates.wrapper).html();
        var isPaginating = pageStore.get(pageStore.keys.isPaginating, false);
        var isNumberPaginating = pageStore.get(pageStore.keys.isNumberPaginating, false);

        if (!isPaginating && !isNumberPaginating) {

            var allFacets = facetsComponent.getAllFacets();
            facetsUtils.instance().setFacets(allFacets);
            filtersUtils.new().setFilters(getFilters());

            var leftFacets = facetsComponent.getLeftFacets();
            var topFacets = facetsComponent.getTopFacets();
            var selectedFacets = selectedFacetsComponent.getHTML();

            wrapperTemplate = Mustache.render(wrapperTemplate, {
                'inOnCategoryPage': isOnCategoryPage(),
                'hasFilters': (selectedFacets !== null),
                'hasFacets': (leftFacets.length > 0 || topFacets.length > 0),
                'summary': summaryComponent.getHTML(),
                'leftFacets': leftFacets,
                'topFacets' : topFacets,
                'hasLeftFacets': (leftFacets.length > 0),
                'hasTopFacets': (topFacets.length > 0),
                'sort': sortComponent.getHTML(),
                'products': resultsComponent.getHTML(),
                'selectedFacets' : selectedFacets,
                'pagination': paginationComponent.getHTML(),
            });

            $('body').addClass('wizzy-search-results-rendered');
            triggerBeforeResultsRenderedEvent(wrapperTemplate);
            domRenderer.updateResultsDOM(wrapperTemplate);
            postResultsRender();
        }
        else {
            $('.wizzy-progress-container').remove();
            $('.wizzy-progress-bg').remove();

            var html = resultsComponent.getHTML();

            triggerBeforeResultsRenderedEvent(html);
            if (isNumberPaginating) {
                if ($(window).width() > 768) {
                    $('.wizzy-search-results').html(html);
                }
                else {
                    $('.wizzy-search-results').append(html);
                }
                $('.wizzy-search-pagination').html(paginationComponent.getHTML());
            }
            else {
                $('.wizzy-search-results').append(html);
            }
        }

        pageStore.set(pageStore.keys.hasMoreResults, resultsComponent.hasMoreResults());
        pageStore.set(pageStore.keys.isPaginating, false);
        pageStore.set(pageStore.keys.isNumberPaginating, false);
        if (isOnCategoryPage()) {
            pageStore.set(pageStore.keys.isCategoryPageRendered, true);
        }
        triggerResultsRenderedEvent();
    }

    function triggerEmptyResultsRenderedEvent() {
        wizzy.triggerEvent(wizzy.allowedEvents.EMPTY_RESULTS_RENDERED, {});
    }

    function triggerResultsRenderedEvent() {
        wizzy.triggerEvent(wizzy.allowedEvents.PRODUCTS_RESULTS_RENDERED, {});
    }

    function triggerBeforeResultsRenderedEvent(html) {
        wizzy.triggerEvent(wizzy.allowedEvents.BEFORE_PRODUCTS_RESULTS_RENDERED, {
            html: html
        });
    }

    function getFilters() {
        var response = pageStore.get(pageStore.keys.searchedResponse, null);
        if (response === null || typeof response.filters === "undefined") {
            return {};
        }

        return response.filters;
    }

    function isOnCategoryPage() {
        return window.wizzyConfig.common.isOnCategoryPage && window.wizzyConfig.common.categoryUrlKey !== "";
    }

    function postResultsRender() {
        rangeComponent.refreshRanges();
        selectComponent.refreshSelectBoxes([
            {
                element: $('.wizzy-sort-select'),
                label: window.wizzyConfig.common.view.templates.literals['sortBy'],
            }
        ]);
        if ($('.wizzy-search-form-wrapper').hasClass('mobileTapped')) {
            $('.wizzy-search-wrapper').addClass('mobileTapped');
            $('.wizzy-search-empty-results-wrapper').addClass('mobileTapped');
            $('body').addClass('wizzyMobileTapped');
            $('html').addClass('wizzyMobileTapped');
        }
        updateAddToCartUenc();
        updateAddToWishlistUenc();
    }

    function btoaHelper(value) {
        return window.btoa(value).replace(/\+/g, '-').replace(/\//g, '_').replace(/=/g, ',');
    }

    function updateAddToWishlistUenc() {
        if (!wizzyConfig.search.addToWishlist.display) {
            return;
        }
        var updatedUenc = btoaHelper(window.location.href);
        var addToWishlistPayload = wizzyConfig.search.addToWishlist.postData;
        addToWishlistPayload['data']['uenc'] = updatedUenc;

        $('.wizzy-product-add-to-wishlist').each(function(e) {
            var button = $(this).find('.wizzy-towishlist-button');
            addToWishlistPayload['data']['product'] = button.data('productid');
            var groupId = button.data('groupid');
            if (groupId != "") {
                addToWishlistPayload['data']['product'] = groupId;
            }
            addToWishlistPayload['data']['searchResponseId'] = pageStore.get(pageStore.keys.lastResponseId);
            button.attr('data-post', JSON.stringify(addToWishlistPayload));
        });
    }

    function updateAddToCartUenc() {
        if (!wizzyConfig.search.addToCart.display) {
            return;
        }
        var updatedUenc = btoaHelper(window.location.href);
        var cartAction = window.wizzyConfig.search.addToCart.formAction;
        var uencMatch = cartAction.match(/\/uenc\/(.*)\//);
        if (uencMatch === null || uencMatch.length <= 1) {
            return;
        }

        uencMatch = uencMatch[1];
        cartAction = cartAction.replace(uencMatch, updatedUenc);
        wizzyConfig.search.addToCart.formAction = cartAction;

        $('.wizzy-tocart-form').each(function(e) {
            var productId = $(this).find('.wizzy-tocart-productid').val();
            var action = cartAction + 'product/' + productId + '/';
            var formUenc = btoaHelper(action);

            $(this).attr('action', action);
            $(this).find('.wizzy-tocart-uenc').val(formUenc);
        });
    }

    function handleFailedSearch() {
        var isPaginating = pageStore.get(pageStore.keys.isPaginating, false);
        var isNumberPaginating = pageStore.get(pageStore.keys.isNumberPaginating, false);

        if ((!isPaginating && !isNumberPaginating)) {
            $('body').addClass('wizzy-search-results-rendered');
            $(domRenderer.getDOMHandler()).html(resultsComponent.getEmptyHTML());
            triggerEmptyResultsRenderedEvent();
        }
        else {
            $('.wizzy-search-pagination').html('');
        }
        pageStore.set(pageStore.keys.searchedResponse, null);
        triggerResultsRenderedEvent();
    }

    return {
        revertDOM: revertDOM,
        showIndicatorOnPageLoad: showIndicatorOnPageLoad,
        showIndicator: showIndicator,
        displayResults: displayResults,
        showLoaderForSpecificDOM:showLoaderForSpecificDOM
    };
});
