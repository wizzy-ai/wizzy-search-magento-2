define(['jquery', 'Mustache'], function($, Mustache) {

    function refreshSelectBoxes(elements) {
        var totalElements = elements.length;

        for (var i = 0; i < totalElements; i++) {
            var element = elements[i]['element'];
            var label = elements[i]['label'];

            var items = [];
            var selectedItem = "";

            element.find('option').each(function(e) {
                var isSelected = false;
                if($(this).is(':selected')) {
                    selectedItem = $(this).text();
                    isSelected = true;
                }
                items.push({
                   label: $(this).text(),
                    isSelected: isSelected,
                });
            });

            var templates = window.wizzyConfig.common.view.templates;
            var selectTemplate = $(templates.select).html();
            selectTemplate = Mustache.render(selectTemplate, {
                items: items,
                label: label,
                selectedItem: selectedItem,
            });

            element.parent().append(selectTemplate);
            element.hide();
        }
    }

    $(document).ready(function(e){
        $('body').on('click', '.wizzy-common-select-option', function(e) {
            var index = $(this).index();
            var text = $(this).text();
            $(this).parents('.wizzy-common-select-container').find('.wizzy-common-select-selectedItem').text(text);
            $(this).parents('.wizzy-common-select-options').hide();
            $(this).parents('.wizzy-common-select-wrapper').siblings('select').find('option:eq('+index+')').prop('selected', true);
            $(this).parents('.wizzy-common-select-wrapper').siblings('select').trigger('change');
        });

        $('body').on('click', '.wizzy-common-select-selector', function(e) {
            $(this).siblings('.wizzy-common-select-options').toggle().position({
                my: "left top",
                at: "left bottom-3",
                of: $(this),
                collision: "none flipfit"
            });
        });

        $(document).mouseup(function(e) {
            var container = $(".wizzy-common-select-options");
            var selectContainer = container.siblings('.wizzy-common-select-selector');
            if (!container.is(e.target) && container.has(e.target).length === 0 && !selectContainer.is(e.target) && selectContainer.has(e.target).length === 0)  {
                container.hide();
            }
        });
    });

    return {
        refreshSelectBoxes: refreshSelectBoxes
    };
});