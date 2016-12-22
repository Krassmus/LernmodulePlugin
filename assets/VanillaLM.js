VanillaLM = {
    state: {},
    getAttribute: function (parameter) {
        if (typeof VanillaLM.state.properties[parameter] !== "undefined") {
            return VanillaLM.state.properties[parameter];
        } else {
            var session = JSON.parse(window.sessionStorage.getItem("VanillaLM.sessionStorage"));
            return session.properties[parameter];
        }
    },
    setAttribute: function (parameter, value) {
        if (typeof VanillaLM.state.properties === "undefined") {
            VanillaLM.state.properties = {};
        }
        VanillaLM.state.properties[parameter] = value;
    },
    markSuccess: function () {
        VanillaLM.state.success = 1;
    },
    set: function (paramater, value) {
        VanillaLM.state[parameter] = value;
    },
    addPoints: function (pointclass, number) {
        if (typeof VanillaLM.state.points === "undefined") {
            VanillaLM.state.points = {};
        }
        VanillaLM.state.points[pointclass] = VanillaLM.getPoints(pointclass) + number;
    },
    getPoints: function (pointclass) {
        if (typeof VanillaLM.state.points[pointclass] !== "undefined") {
            return VanillaLM.state.points[pointclass];
        } else {
            var session = JSON.parse(window.sessionStorage.getItem("VanillaLM.sessionStorage"));
            if ((typeof session.points !== "undefined") && (typeof session.points[pointclass] !== "undefined")) {
                return session.points[pointclass];
            } else {
                return 0;
            }
        }
    },
    /**
     * Sends the current state to the opener-window and saves the new state to the sessionStorage.
     */
    send: function () {
        var opener = window.opener || window.parent;
        var session = JSON.parse(window.sessionStorage.getItem("VanillaLM.sessionStorage"));
        VanillaLM.state = Object.assign(session, VanillaLM.state);
        window.sessionStorage.setItem("VanillaLM.sessionStorage", JSON.stringify(VanillaLM.state));
        if (opener && VanillaLM.state.secret) {
            opener.postMessage(JSON.stringify(VanillaLM.state), "*");
        }
    },
    /**
     * Returns a GET parameter of the current request. Only used to extract the secret-parameter.
     * @param parameterName
     * @returns string: value of GET parameter
     */
    findGetParameter: function(parameterName) {
        var result = null,
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function (item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
    }
};

/**
 * Now some startup procedure: If this page has a secret in its URL, we need to save that.
 */
document.addEventListener("DOMContentLoaded", function(event) {
    //save secret in sessionStorage
    var secret = VanillaLM.findGetParameter("secret");
    if (secret) {
        VanillaLM.state.secret = secret;
        window.sessionStorage.setItem("VanillaLM.sessionStorage", JSON.stringify(VanillaLM.state));
    }
});