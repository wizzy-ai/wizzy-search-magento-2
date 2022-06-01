requirejs(['jquery', 'wizzy/libs/searchUrlUtils', 'wizzy/utils/search'], function($, urlUtils, searchUtils) {
    function hasToEnableFullScreen() {
        return (window.wizzyConfig.autocomplete.enabled || window.wizzyConfig.search.configs.general.behaviour === "search_as_you_type");
    }

    $('body').on('click', '.wizzy-search-glass, '+ searchUtils.getInputDOM(), function(e) {
        e.preventDefault();
        if (hasToEnableFullScreen()) {
            var windowWidth = $(window).width();
            if (windowWidth < 768) {
                $(this).parents('.wizzy-search-form-wrapper').addClass('mobileTapped');
                $('.wizzy-search-empty-results-wrapper').addClass('mobileTapped');
                $('body').addClass('wizzyMobileTapped');
                $('html').addClass('wizzyMobileTapped');
            }
            $(this).parents('.wizzy-search-form-wrapper').find(searchUtils.getInputDOM()).focus();
            $('.wizzy-search-wrapper').addClass('mobileTapped');
        }
    });

    $('body').on('click', '.wizzy-search-glass', function(e) {
        if (!hasToEnableFullScreen()) {
            $(this).parents('form').submit();
        }
    });

    $('body').on('click', '.wizzy-search-back', function(e) {
        e.preventDefault();
        var searchInput = $(this).parents('.wizzy-search-form-wrapper').find(searchUtils.getInputDOM());
        $(this).parents('.wizzy-search-form-wrapper').removeClass('mobileTapped');
        $('.wizzy-search-wrapper').removeClass('mobileTapped');
        $('.wizzy-search-empty-results-wrapper').removeClass('mobileTapped');
        $('body').removeClass('wizzyMobileTapped');
        $('html').removeClass('wizzyMobileTapped');

        if (!urlUtils.isOnSearchPage()) {
            searchInput.val('');
            searchInput.trigger('keyup');
        }
    });

    $('body').on('click', '.wizzy-search-clear', function(e) {
        e.preventDefault();
        var searchInput = $(this).parents('.wizzy-search-form-wrapper').find(searchUtils.getInputDOM());
        searchInput.val('');
        searchInput.trigger('keyup');
        searchInput.focus();
    });

    $('body').on('keyup', searchUtils.getInputDOM(),function (e) {
        var value = $(this).val().trim();
        if (value == "") {
            $(this).parents('.wizzy-search-form-wrapper').find('.wizzy-search-clear').fadeOut(100);
        }
        else {
            $(this).parents('.wizzy-search-form-wrapper').find('.wizzy-search-clear').fadeIn(100);
        }
    });
});