define(['jquery', 'wizzy/fetchers/autocomplete', 'wizzy/fetchers/filters', 'wizzy/fetchers/search', 'wizzy/libs/pageStore', 'wizzy/utils/keyboard', 'wizzy/libs/searchUrlUtils'], function($, autocompleteFetcher, filtersFetcher, sF, pageStore, keyUtils, urlUtils) {

    var searchElement;
    var menu;
    var position;
    var selectable;
    var searchterm;
    var textWrapper;
    var selectedIndex = -1;
    var dataFocus = 'data-focus';
    var autocompleteOptions;
    var elementInputTypingTimer;
    var suggestionLink;

    function assignTextWrapperClick() {
        $('body').on('click', textWrapper, function(e) {
            e.preventDefault();
            e.stopPropagation();

            var searchValue = $(this).parent().data(searchterm);
            searchElement.val(searchValue);
            executeAutoComplete(searchValue);
        });
    }

    function executeAutoComplete(value) {
        autocompleteFetcher({
            'q': value,
            'element': searchElement,
        });
    }

    function autocomplete(options) {
        autocompleteOptions = options;
        searchElement = options['element'];
        menu = $(options['menu']);
        position = typeof options['position'] !== "undefined" ? options['position'] : "right";
        selectable = typeof options['selectable'] !== "undefined" ? options['selectable'] : "selectable";
        searchterm = typeof options['searchterm'] !== "undefined" ? options['searchterm'] : "searchterm";
        textWrapper = typeof options['text-wrapper'] !== "undefined" ? options['text-wrapper'] : "autocomplete-text-wrapper";
        suggestionLink = typeof options['suggestionLink'] !== "undefined" ? options['suggestionLink'] : ".autocomplete-link";

        assignTextWrapperClick();

        searchElement.keyup(function(e) {
            var value = searchElement.val().trim();
            if (value == "") {
                pageStore.set(pageStore.keys.searchInputValue, '');
                hideMenu();
            }
            else {
                if (keyUtils.isCtrlKey(e)) {
                    return;
                }
                if (keyUtils.isEnterKey(e.keyCode)) {
                    hideMenu();
                    return;
                }
                if (keyUtils.isUpOrDownKey(e.keyCode) && !isMenuHidden()) {
                    e.preventDefault();
                    navigateSelectableItems(e.keyCode);
                    return;
                }
                if (keyUtils.isNonAllowedSearchKey(e.keyCode)) {
                    return;
                }
                if (value == pageStore.get(pageStore.keys.searchInputValue, '')) {
                    return;
                }
                initAutocompleteReq();
            }
        });

        searchElement.on('paste', function(e) {
            $(this).trigger('keyup');
            setTimeout(function(e) {
                initAutocompleteReq();
            }, 0);
        });

        searchElement.on('cut', function() {
            $(this).trigger('keyup');
            setTimeout(function(e) {
                initAutocompleteReq();
            }, 0);
        });

        searchElement.click(function(e) {
            var value = searchElement.val().trim();
            if (value != "" && isMenuHidden()) {
                showMenu();
            }
        });

        $(document).on('keydown', function(e) {
            if (e.keyCode === keyUtils.escapeKeyCode) {
                hideMenu();
            }
        });

        $(document).mousedown(function(e) {
            hideMenuOnClickEvent(e);
        });

        addAutocompleteLinkListeners(suggestionLink);
    }

    function initAutocompleteReq() {
        var value = searchElement.val().trim();
        pageStore.set(pageStore.keys.searchInputValue, value);
        shootAutocompleteRequest();
    }

    function addAutocompleteLinkListeners(suggestionLink) {
        $('body').on('click', suggestionLink, function(e) {
            e.preventDefault();
            var index = $(this).data('index');
            handleSuggestionClick(index);
        });
    }

    function shootAutocompleteRequest() {
        clearTimeout(elementInputTypingTimer);
        elementInputTypingTimer = setTimeout(executeAutocompleteRequest, 200);
    }

    function executeAutocompleteRequest() {
        resetSelectableItems();
        if (pageStore.get(pageStore.keys.searchInputValue) != "") {
            executeAutoComplete(pageStore.get(pageStore.keys.searchInputValue));
        }
    }

    function showMenu() {
        if (pageStore.get(pageStore.keys.searchInputValue) == "") {
            return;
        }
        menu = $(autocompleteOptions['menu']);
        if (isMenuHidden()) {
            menu.css('display', 'flex').hide().fadeIn(100);
        }
        menu.position({
            my: position + " top",
            at: position + " bottom",
            of: searchElement,
            collision: "flipfit none"
        });
    }

    function resetSelectableItems() {
        var selector = getSelectableDataSelector();
        menu.find(selector).attr(dataFocus, false);
        selectedIndex = -1;
    }

    function hideMenu() {
        resetSelectableItems();
        if (pageStore.get(pageStore.keys.searchInputValue) == "") {
            menu.html('');
        }
        menu.fadeOut(100);
    }

    function navigateSelectableItems(code) {
        var index;
        if (code == keyUtils.downArrowKeyCode) {
            index = getNextSelectableIndex();
        }
        else {
            index = getPrevSelectableIndex();
        }

        selectedIndex = index;
        var selector = getSelectableDataSelector();
        menu.find(selector).attr(dataFocus, false);
        var searchValue = pageStore.get(pageStore.keys.searchInputValue);
        if (selectedIndex > -1) {
            menu.find(selector).eq(selectedIndex).attr(dataFocus, true);
            searchValue = menu.find(selector).eq(selectedIndex).data(searchterm);
            displayTopProductsOfSuggestion(selectedIndex);
        }
        else {
            shootAutocompleteRequest();
        }
        searchElement.val(searchValue);
    }

    function displayTopProductsOfSuggestion(index) {
        var autocompleteFilters = pageStore.get(pageStore.keys.suggestionFilters, []);
        if (typeof autocompleteFilters[index] !== "undefined") {
            if (autocompleteFilters[index]['group'] != "pages") {
                filtersFetcher.execute({
                    'for': 'menu',
                    'filters': autocompleteFilters[index]['filters'],
                });
            }
        }
    }

    function handleSuggestionClick(index) {
        var autocompleteFilters = pageStore.get(pageStore.keys.suggestionFilters, []);
        if (typeof autocompleteFilters[index] !== "undefined") {
            if (autocompleteFilters[index]['group'] == "pages" && autocompleteFilters[index]['filters']['pages'].length > 0) {
                window.location.href = autocompleteFilters[index]['filters']['pages'][0].url;
            }
            else {
                hideMenu();
                var searchValue = autocompleteFilters[index]['value'].toLowerCase();
                pageStore.set(pageStore.keys.searchInputValue, searchValue);
                searchElement.val(searchValue);
                sF.execute({
                    q: searchValue
                });
                if (searchValue.length !== 0) {
                    urlUtils.updateQuery(searchValue);
                }
            }
        }
    }

    function getPrevSelectableIndex() {
        var selectableItems = getAllSelectableItems();
        var totalSelectableItems = selectableItems.length;
        var prevIndex;

        if (selectedIndex == -1) {
            prevIndex = totalSelectableItems - 1;
            return prevIndex;
        }

        prevIndex = selectedIndex - 1;
        return prevIndex;
    }

    function getNextSelectableIndex() {
        var selectableItems = getAllSelectableItems();
        var totalSelectableItems = selectableItems.length;
        var nextIndex;

        if (selectedIndex != (totalSelectableItems - 1)) {
            nextIndex = selectedIndex + 1;
            return nextIndex;
        }

        nextIndex = -1;
        return nextIndex;
    }

    function getAllSelectableItems() {
        var selector = getSelectableDataSelector();
        return menu.find(selector);
    }

    function getSelectableDataSelector() {
        var selector = "[data-"+selectable+"]";
        return selector;
    }

    function hideMenuOnClickEvent(e) {
        if (!menu.is(e.target) && menu.has(e.target).length === 0 && !searchElement.is(e.target) && searchElement.has(e.target).length === 0) {
            hideMenu();
        }
    }

    function isMenuHidden() {
        return menu.css('display') == "none";
    }

    return {
        autocomplete: autocomplete,
        showMenu: showMenu,
        hideMenu: hideMenu,
        isMenuHidden: isMenuHidden,
    };

});