define(['wizzy/bundle'], function(wizzyBundle) {

    var WizzyDataStorage = wizzyBundle.WizzyDataStorage;

    return {
        data: (WizzyDataStorage.singleton()),
    };
});