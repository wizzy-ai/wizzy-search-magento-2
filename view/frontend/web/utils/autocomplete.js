define(['wizzy/data'], function(wizzyData) {

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
    function isDefaultBehaviourSet() {
        return (typeof window.wizzyConfig.autocomplete.configs.defaultBehaviour !== "undefined");
    }

    function getDefaultSuggestionsPool(){
        var defaultBehaviour = window.wizzyConfig.autocomplete.configs.defaultBehaviour;
        var pool = defaultBehaviour.suggestions.defaultPool;
        return pool;
    }
    
    function getDefaultSuggestions(availableTotalPinnedTermsCount = null){
        let pool = getDefaultSuggestionsPool();
        var totalSuggestions = availableTotalPinnedTermsCount ? availableTotalPinnedTermsCount : pool.length;
        let payload = {}
            for (var i = 0; i < totalSuggestions; i++) {
                var suggestion = pool[i];
                if (typeof payload[suggestion['section']] === "undefined") {
                    payload[suggestion['section']] = [];
                }
                payload[suggestion['section']].push(suggestion);
            }
            return payload;
    }
    
    function getRecentSearchesPool(){
       return  wizzyData.data.get("SEARCHED_KEYWORDS") ? wizzyData.data.get("SEARCHED_KEYWORDS") : [];
    }
    
    function getRecentSearches(availableRecentSearchTermsCount){
        let recentSearches = getRecentSearchesPool();
    
        let mappedRecentSearches = [];
            if(recentSearches){
                recentSearches.reverse()
                for(let i=0; i<availableRecentSearchTermsCount; i++){
                    mappedRecentSearches.push(
                        {
                            value:recentSearches[i],
                            valueHighlighted:recentSearches[i],
                            filters:[],
                            payload:[],
                            section:'recentSearches'
                        }
                    )
                }
    
            return mappedRecentSearches;
            }
    }

    function getProductClickDOM() {
        if (typeof window.wizzyConfig.autocomplete.configs !=='undefined' && typeof window.wizzyConfig.autocomplete.configs.general.clickDom !== 'undefined'){
            return window.wizzyConfig.autocomplete.configs.general.clickDom
        }
        
        return '.topproduct-item a'
    }

    return {
        getSectionsToAdd: getSectionsToAdd,
        isDefaultBehaviourSet: isDefaultBehaviourSet,
        getDefaultSuggestionsPool:getDefaultSuggestionsPool,
        getRecentSearchesPool:getRecentSearchesPool,
        getRecentSearches:getRecentSearches,
        getDefaultSuggestions:getDefaultSuggestions,
        getProductClickDOM:getProductClickDOM
    };

});
