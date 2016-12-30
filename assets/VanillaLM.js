VanillaLM = {
    state: {},
    /**
     * Returns the value of the given attribute. This attribute may be set immediatly before or on another page
     * during the same session.
     * @param parameter: name of the attribute
     * @returns {*}
     */
    getAttribute: function (parameter) {
        if (typeof VanillaLM.state.properties[parameter] !== "undefined") {
            return VanillaLM.state.properties[parameter];
        } else {
            var session = JSON.parse(window.sessionStorage.getItem("VanillaLM.sessionStorage"));
            return session.properties[parameter];
        }
    },
    /**
     * Sets an attribute for the user. For example an attribute could be "current page".
     * @param string parameter: name of the attribute
     * @param value: value of the attribute
     */
    setAttribute: function (parameter, value) {
        if (typeof VanillaLM.state.properties === "undefined") {
            VanillaLM.state.properties = {};
        }
        VanillaLM.state.properties[parameter] = value;
    },
    /**
     * Marks the module as successfully finished. Don't forget to trigger the method send as well.
     */
    markSuccess: function () {
        VanillaLM.state.success = 1;
    },
    /**
     * This adds points to the score of the user. If this user already has some points of the pointclass (type)
     * then they will be added )or subtracted if number is negative).
     * @param string pointclass: name of the type of points like
     * @param number
     */
    addPoints: function (pointclass, number) {
        if (typeof VanillaLM.state.points === "undefined") {
            VanillaLM.state.points = {};
        }
        VanillaLM.state.points[pointclass] = VanillaLM.getPoints(pointclass) + number;
    },
    /**
     * Gets the amount of points of pointclass the user already has received (combined in this session).
     * @param string pointclass: the type of points.
     * @returns integer|float
     */
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
     * Sets a property if the current state. Note that this can erase all points, mark the module as successful
     * or erase all attributes by accident. Use method setAttribute or addPoints instead if possible.
     * @param paramater
     * @param value
     */
    set: function (paramater, value) {
        VanillaLM.state[parameter] = value;
    },
    get: function (paramater, value) {
        if (typeof VanillaLM.state[parameter] !== "undefined") {
            return VanillaLM.state[parameter];
        } else {
            var session = JSON.parse(window.sessionStorage.getItem("VanillaLM.sessionStorage"));
            return session[parameter];
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