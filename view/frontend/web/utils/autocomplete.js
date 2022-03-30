define([''], function() {

    function getSectionsToAdd() {
        var sectionsOrder = wizzyConfig.autocomplete.menu.sections;
        var sections = [];
        if (typeof sectionsOrder !== "undefined") {
            var keys = Object.keys(sectionsOrder);
            var totalSections = keys.length;
            for (var i = 0; i < totalSections; i++) {
                var sectionKey = sectionsOrder[keys[i]];
                sections.push({
                    key: sectionKey,
                });
            }

            return sections;
        }

        return sections;
    }

    return {
        getSectionsToAdd: getSectionsToAdd,
    };

});
