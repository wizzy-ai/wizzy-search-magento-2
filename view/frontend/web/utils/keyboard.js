define([], function() {
    function isUpOrDownKey(code) {
        if (code == 40 || code == 38) {
            return true;
        }

        return false;
    }

    function isNonAllowedSearchKey(code) {
        if(((code <= 90 && code >= 48) || (code >= 96 && code <= 105) || code == 8 || code == 46 || code == 32) && code != 229) {
            return false;
        }

        return true;
    }

    function isCtrlKey(e) {
        return e.ctrlKey && e.keyCode != 70;
    }

    function isEnterKey(code) {
        return code === 13;
    }

    return {
        isUpOrDownKey: isUpOrDownKey,
        isNonAllowedSearchKey: isNonAllowedSearchKey,
        escapeKeyCode: 27,
        downArrowKeyCode: 40,
        upArrowKeyCode: 38,
        isEnterKey: isEnterKey,
        isCtrlKey: isCtrlKey,
    };
});