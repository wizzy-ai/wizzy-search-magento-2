define(['jquery', 'wizzy/libs/pageStore', 'wizzy/libs/searchUrlUtils', 'wizzy/libs/autocomplete', 'wizzy/fetchers/search', 'wizzy/utils/keyboard', 'wizzy/fetchers/filters', 'wizzy/utils/pagination', 'wizzy/fetchers/variation', 'wizzy/renderers/components/filters/facets'], function($, pageStore, urlUtils, wizzyAutocomplete, sF, keyUtils, fF, paginationUtils, vF, facetsComponent) {
    var searchElement;
    var behavior;
    var elementInputTypingTimer;
    var formSubmissionBehaviour;

    function search(options) {
        searchElement = options['element'];
        behavior = options['behavior'];
        formSubmissionBehaviour = options['formSubmissionBehaviour'];

        if (behavior == "ontype") {
            searchElement.keyup(function(e) {
                var isByTrigger = (typeof e.isTrigger !== "undefined" && e.isTrigger);
                if (keyUtils.isCtrlKey(e) || (keyUtils.isNonAllowedSearchKey(e.keyCode) && !isByTrigger)) {
                    return;
                }
                performSearchRequest(false, isByTrigger);
            });

            searchElement.bind('paste', function() {
                setTimeout(function(e) {
                    performSearchRequest(false, false);
                }, 0);
            });
            searchElement.bind('cut', function() {
                setTimeout(function(e) {
                    performSearchRequest(false, false);
                }, 0);
            });

            searchElement.parents('form').submit(function(e) {
                e.preventDefault();
            });
        }
        else {
            searchElement.parents('form').submit(function(e) {
               e.preventDefault();
               if (window.wizzyConfig.autocomplete.enabled) {
                   wizzyAutocomplete.hideMenu();
                   pageStore.set(pageStore.keys.lastRequestIdAutocomplete, '');
               }
               performSearchRequest(true, false);
            });
        }
        urlUtils.listenChanges(searchElement);
    }

    function performSearchRequest(isBySubmit, isByTrigger) {
        var value = searchElement.val().trim();
        if (pageStore.get(pageStore.keys.searchInputValue) !== value || (isBySubmit && pageStore.get(pageStore.keys.searchSubmitValue) !== value)) {
            pageStore.set(pageStore.keys.searchInputValue, value);
            pageStore.set(pageStore.keys.searchSubmitValue, value);

            if (value.length == 0 && isBySubmit) {
                return;
            }
            shootSearchRequest(isBySubmit, isByTrigger);
            if (behavior != "ontype") {
                searchElement.blur();
            }
        }
    }

    function executeSearchRequest(isByTrigger) {
        var searchInputValue = pageStore.get(pageStore.keys.searchInputValue);
        if (searchInputValue.length >= 3 || searchInputValue.length === 0) {
            if (formSubmissionBehaviour != 'redirect_page') {
                sF.execute({
                    q: searchInputValue,
                    fS: true,
                });
            }
            if (searchInputValue.length !== 0 && !isByTrigger) {
                if (formSubmissionBehaviour != 'redirect_page') {
                    urlUtils.updateQuery(searchInputValue);
                }
                else {
                    urlUtils.redirectToQuery(searchInputValue);
                }
            }
        }

        if (searchInputValue.length === 0) {
            var beforeSearchDOM = pageStore.get(pageStore.keys.beforeSearchDOM);
            if (beforeSearchDOM !== "" && !urlUtils.hasSearchEndPointInUrl(beforeSearchDOM.url)) {
                urlUtils.updatePage(beforeSearchDOM.url, beforeSearchDOM.title);
            }
        }
    }

    function shootSearchRequest(isBySubmit, isByTrigger) {
        if (isBySubmit) {
            executeSearchRequest();
        }
        else {
            clearTimeout(elementInputTypingTimer);
            elementInputTypingTimer = setTimeout(executeSearchRequest(isByTrigger), 200);
        }
    }

    function init() {
        addFilterSearchIconClickListener();
        addOnPageFacetSearch();
        addFilterListItemClickListener();
        addSelectedFacetItemClickListener();
        addLeftFacetsClickListener();
        addTopFacetsClickListener();
        declareFilterFunctions();
        addSortChangeListener();
        addInfiniteScrollListener();
        addMoveToTopListener();
        addPaginationClickListener();
        addSwatchClickListener();
        addClearAllListener();
        addMobileFilterListeners();
    }

    function addMobileFilterListeners() {
        $('body').on('click', '.wizzy-filters-bg, .wizzy-filters-close-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $(this).fadeOut(200);
            $(this).siblings('.wizzy-search-filters-left-wrapper, .wizzy-filters-header').animate({
                "left": "-80%",
            }, 200);
            $(this).parents('.wizzy-search-filters-left').hide();
        });

        $('body').on('click', '.wizzy-filters-mobile-entry', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if ($('.wizzy-search-filters-left').size() > 0) {
                facetsComponent.moveTopFacetLeft();
                $('.wizzy-search-filters-left').show();
                $('.wizzy-search-filters-left').find('.wizzy-filters-bg').show();
                $('.wizzy-search-filters-left').find('.wizzy-filters-close-btn').show();
                $('.wizzy-search-filters-left').find('.wizzy-search-filters-left-wrapper, .wizzy-filters-header').animate({
                    "left": 0,
                }, 200);
            }
        });
    }

    function addClearAllListener() {
        $('body').on('click', '.wizzy-filters-clear-all', function(e) {
            e.preventDefault();
            e.stopPropagation();
            fF.clearAll();
        });
    }

    function addSwatchClickListener() {
        $('body').on('click', '.product-item-swatch-item', function(e) {
            e.preventDefault();
            e.stopPropagation();

            var variationId = $(this).data('variationid');
            var groupId = $(this).parents('.wizzy-result-product').data('groupid');

            if (typeof groupId !== "undefined" && typeof  variationId !== "undefined" && groupId !== null && variationId !== null) {
                wizzy.triggerEvent(wizzy.allowedEvents.PRODUCT_SWATCH_CLICKED, {
                    element: $(this),
                });
                vF.execute({
                    groupId: groupId,
                    variationId: variationId,
                });
            }
        });
    }

    function addPaginationClickListener() {
        $('body').on('click', '.wizzy-pagination-list a', function(e) {
           e.preventDefault();
           var parent = $(this).parent();
           if (parent.hasClass('inactive') || parent.hasClass('active')) {
               return;
           }

           if (parent.hasClass('pagination-arrow')) {
               if (parent.hasClass('previous-arrow')) {
                   fF.applyPrevPage();
               }
               else {
                   fF.applyNextPage();
               }
               return;
           }

           var page = parent.data('page');
           fF.jumpToPage(page);
        });
    }

    function addMoveToTopListener() {
        $(window).on("scroll", function() {
            if (urlUtils.isOnSearchPage() && $(window).width() > 768) {
               if (getBodyScrollTop() > 100) {
                    $('.wizzy-scroll-to-top-wrapper').fadeIn();
                } else {
                    $('.wizzy-scroll-to-top-wrapper').fadeOut();
                }
            }
        });

        $('body').on('click', '.wizzy-scroll-to-top-wrapper', function(e) {
            e.preventDefault();
            $("html, body").animate({
                scrollTop: 0
            }, 100);
        });
    }

    function getBodyScrollTop() {
       return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
    }

    function addInfiniteScrollListener() {
        if (paginationUtils.isInfiniteScroll()) {
            $(window).on("scroll", function() {
                if (urlUtils.isOnSearchPage() || wizzyConfig.common.isOnCategoryPage) {
                    var scrollHeight = $(document).height();
                    var windowHeight = window.innerHeight;
                    var bodyScrollTop = getBodyScrollTop();
                    var scrollPos = windowHeight + (bodyScrollTop);
                    var scrollOffset = paginationUtils.getScrollOffset();

                    if((((scrollHeight - scrollOffset) >= scrollPos) / scrollHeight) == 0){
                        var isExecuting = (pageStore.get(pageStore.keys.isPaginating, false) || pageStore.get(pageStore.keys.lastRequestIdFilters, null) !== null || pageStore.get(pageStore.keys.lastRequestIdSearch, null) !== null);
                        var hasMoreResults = pageStore.get(pageStore.keys.hasMoreResults, false);
                        if (!isExecuting && hasMoreResults) {
                            fF.applyNextPage();
                        }
                    }
                }
            });
        }
    }

    function addSortChangeListener() {
        $('body').on('change', '.wizzy-sort-select', function(e) {
            var value = $(this).val();
            var order = $(this).find(':selected').data('order');
            wizzy.triggerEvent(wizzy.allowedEvents.BEFORE_SORT_EXECUTED, {
                value: value,
                order: order
            });
            updateSortMethod(value, order);
            $.fn.applySort();
        });
    }

    function updateSortMethod(value, order) {
        var sortConfigs = window.wizzyConfig.search.configs.sorts.configs;
        var keys = Object.keys(sortConfigs);
        var totalKeys = keys.length;

        for (var i = 0; i < totalKeys; i++) {
            var key = keys[i];
            if (sortConfigs[key].field === value && order === sortConfigs[key].order) {
                pageStore.set(pageStore.keys.selectedSortMethod, sortConfigs[key]);
                break;
            }
        }
    }

    function addSelectedFacetItemClickListener() {
        $('body').on('click','.wizzy-selected-facet-list-item', function(e) {
            e.preventDefault();
            var filterKey = $(this).data('key');
            var facetKey = $(this).data('facetkey');

            wizzy.triggerEvent(wizzy.allowedEvents.AFTER_FILTER_ITEM_CLICKED, {
                facetKey: facetKey,
                filterKey: filterKey
            });

            $.fn.applyWizzyFilters(facetKey, filterKey, true);
        });
    }

    function addFilterListItemClickListener() {
        $('body').on('click','.wizzy-facet-list-item', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if ($(this).hasClass('facet-range-item')) {
                return;
            }

            var checkbox = $(this).find('> .wizzy-facet-list-item-label > .wizzy-facet-list-item-checkbox > input[type="checkbox"]');
            toggleCheckbox(checkbox);

            var facetKey = $(this).parents('.wizzy-filters-facet-block').data('key');
            if (typeof facetKey === "undefined" && $(this).parents('.filters-list-top-values-wrapper').size() > 0) {
                facetKey = $(this).parents('.filters-list-top-values-wrapper').data('key');
                checkbox = $(this).parents('.wizzy-search-filters-top').find('.facet-body-' + facetKey + ' > .wizzy-facet-list > .wizzy-facet-list-item:eq('+$(this).index()+')').find('> .wizzy-facet-list-item-label > .wizzy-facet-list-item-checkbox > input[type="checkbox"]');
                toggleCheckbox(checkbox);
            }
            var filterKey = $(this).data('key');

            if (typeof facetKey === "undefined" || typeof filterKey === "undefined") {
                return;
            }

            $(this).toggleClass('active');
            $.fn.applyWizzyFilters(facetKey, filterKey, false);
        });
    }

    function declareFilterFunctions() {
        $.fn.clearWizzyFilters = function(key) {
            fF.clear(key);
            return this;
        };

        $.fn.applyWizzyFilters = function(facetKey, filterKey, isFromSelected) {
            fF.apply(facetKey, filterKey, isFromSelected);
            return this;
        };

        $.fn.applySort = function() {
            fF.applySort();
            return this;
        };

        $.fn.setSearchedTitle = function(title) {
            pageStore.set(pageStore.keys.searchInputValue, title);
            pageStore.set(pageStore.keys.searchSubmitValue, title);
            return this;
        };

        $.fn.refreshFilters = function(isFromPageLoad) {
            fF.refreshFilters(isFromPageLoad);
            return this;
        };

        $.fn.categorySearch = function(categoryKey) {
            pageStore.set(pageStore.keys.isCategoryPageRendered, false);
            pageStore.set(pageStore.keys.searchedResponse, null);
            fF.categorySearch(categoryKey);
            return this;
        };
    }

    function toggleCheckbox(checkbox) {
        if (checkbox.size() > 0) {
            if (checkbox.is(":checked")) {
                checkbox.prop('checked', false);
            }
            else {
                checkbox.prop('checked', true);
            }
        }
    }

    function addLeftFacetsClickListener() {
        $('body').on('click', '.facet-block-left .wizzy-facet-head', function(e) {
            e.preventDefault();
            if ($(window).width() > 768) {
                $(this).parent().toggleClass('collapsed');
                if ($(this).parent().hasClass('first-opened') && $(this).parent().index() == 0) {
                    $(this).parent().addClass('collapsed');
                }
                $(this).parent().removeClass('first-opened');
            }
        });
    }

    function addTopFacetsClickListener() {
        $('body').on('click', '.facet-block-top .wizzy-facet-head', function(e) {
            e.preventDefault();
            var valuesWrapper = $(this).parents('.wizzy-search-filters-top').find('.filters-list-top-values-wrapper');
            valuesWrapper.data('key', $(this).parents('.wizzy-filters-facet-block').data('key'));

            if ($(this).hasClass('active')) {
                valuesWrapper.html('');
            }
            else {
                $('.facet-block-top .wizzy-facet-head').removeClass('active');
                valuesWrapper.html($(this).siblings('.wizzy-facet-body').clone());
            }
            $(this).toggleClass('active');
        });
    }

    function addFilterSearchIconClickListener() {
        $('body').on('click','.facet-head-search-icon',function(e) {
            e.preventDefault();
            var parent = $(this).parents('.facet-search-wrapper');
            var searchInput = parent.find('input');
            if (parent.hasClass('active')) {
                searchInput.val('');
                searchInput.keyup();
            }
            parent.toggleClass('active');
            if (parent.hasClass('active')) {
                searchInput.focus();
            }
        });
    }

    function addOnPageFacetSearch() {
        $('body').on('keyup', '.facet-head-search-input', function(e) {
            var value = $(this).val().trim().toLowerCase();
            var list = $(this).parents('.wizzy-filters-facet-block').find('.wizzy-facet-list');
            if (value === "") {
                list.find('li').show();
            }
            else {
                list.find('li').each(function(index, item) {
                    var term = $(item).data('term');
                    if (typeof term === "undefined") {
                        term = "";
                    }
                    term = term.toLowerCase();
                    if (term.includes(value)) {
                        $(this).show();
                    }
                    else {
                        $(this).hide();
                    }
                });
            }
        });
    }

    return {
        search: search,
        init: init,
    };

});
