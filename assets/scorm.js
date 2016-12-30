API = {
    LMSInitialize: function () {

    },
    LMSFinish: function () {
        jQuery.post(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/" + jQuery("#attempt_id").val());
    },
    LMSGetValue: function (key) {
        return window.localStorage.getItem("test" + key);
    },
    LMSSetValue: function (key, value) {
        window.localStorage.setItem("test" + key, value);
    },
    LMSCommit: function () {

    },
    LMSGetLastError: function () {

    },
    LMSGetErrorString: function () {

    },
    LMSGetDiagnostic: function () {

    }

};