<? $sandbox = array(
    "allow-forms",
    "allow-modals",
    "allow-orientation-lock",
    "allow-pointer-lock",
    "allow-popups",
    //"allow-popups-to-escape-sandbox",
    "allow-presentation",
    trim(Config::get()->LERNMODUL_DATA_URL) ? "allow-same-origin" : "", //if LERNMODUL_DATA_URL is set we assume that it leads to a subdomain or different domain at all and thus we don't need this flag anymore.
    "allow-scripts",
    //"allow-top-navigation",
) ?>

<? $framesecret = md5(uniqid()) ?>

<script>
    STUDIP.Lernmodule = {
        requestFullscreen: function () {
            var module = jQuery("iframe")[0];
            if (module.requestFullscreen) {
                module.requestFullscreen();
            } else if (module.msRequestFullscreen) {
                module.msRequestFullscreen();
            } else if (module.mozRequestFullScreen) {
                module.mozRequestFullScreen();
            } else if (module.webkitRequestFullscreen) {
                module.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
            }
        },
        editConfigs: function () {
            STUDIP.Dialog.fromURL(
                STUDIP.URLHelper.getURL(
                    STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/html/set_configs",
                    {
                        "module_id": '<?= htmlReady($module->getId()) ?>',
                        "cid": '<?= htmlReady(Context::get()->id) ?>',
                        "configs": STUDIP.Lernmodule.default_configs
                    }
                )
            );
        }
    };

    <? if ($module['customdata']['end_file']) : ?>
    var end_file_found = false;
    window.setInterval(function () {
        if (!end_file_found) {
            //search for iframe-page
            var page = document.getElementById("lernmodule_iframe").contentWindow.location.href;
            var end_file = "<?= htmlReady($module['customdata']['end_file']) ?>";
            page = page.split("?")[0];
            if (page.indexOf(end_file) !== -1) {
                end_file_found = true;
                jQuery.post(
                    STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>"),
                    {"success" : 1}
                );
            }
        }
    }, 500);
    <? else : ?>
    window.setTimeout(function () {
        if (!STUDIP.Lernmodule.received_message_api_messages) {
            jQuery.post(
                STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>"),
                {"message": {"success": 1}}
            );
        }
    }, 30 * 1000);
    STUDIP.Lernmodule.received_message_api_messages = false;
    STUDIP.Lernmodule.dont_blubber = false;
    <? endif ?>
    window.addEventListener("message", function (event) {
        var origin = event.origin || event.originalEvent.origin;
        var myorigin = "<?= htmlReady($myorigin) ?>";
        var message = JSON.parse(event.data);
        if (message.secret === '<?= $framesecret ?>') {
            STUDIP.Lernmodule.received_message_api_messages = true;
            //it's from the correct window, yay!
            delete message.secret;
            STUDIP.Lernmodule.lastState = message;
            jQuery.post(
                STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>"),
                { "message": message }
            );
            if (typeof message.request !== "undefined") {
                if (message.request === "/actor/current") {
                    document.getElementById("lernmodule_iframe").contentWindow.postMessage(JSON.stringify({
                        "secret": '<?= $framesecret ?>',
                        "request_id": message.request_id,
                        "id": '<?= $GLOBALS['user']->id ?>',
                        "name": '<?= htmlReady($GLOBALS['user']->getFullName()) ?>',
                        "email": '<?= htmlReady(get_visible_email($GLOBALS['user']->id)) ?>',
                        "avatar": '<?= htmlReady(Visibility::verify('picture', $GLOBALS['user']->id, "nobody")
                            ? Avatar::getAvatar($GLOBALS['user']->id)->getURL(Avatar::NORMAL)
                            : null) ?>',
                        "language": '<?= htmlReady(strtr($_SESSION['_language'], '_', '-')) ?>'
                    }), "*");
                }
                if (message.request === "/state") {
                    document.getElementById("lernmodule_iframe").contentWindow.postMessage(JSON.stringify(jQuery.extend(
                        STUDIP.Lernmodule.lastState,
                        {
                            "secret": '<?= $framesecret ?>',
                            "request_id": message.request_id
                        }
                    )), "*");
                }
                if (message.request === "/style") {
                    document.getElementById("lernmodule_iframe").contentWindow.postMessage(JSON.stringify({
                        "secret": '<?= $framesecret ?>',
                        "request_id": message.request_id,
                        "color": jQuery("body").css("color"),
                        "background-color": jQuery("#layout_content").css("background-color"),
                        "font-family": jQuery("body").css("font-family"),
                        "primary-color": jQuery("body").css("background-color"),
                        "secondary-color": jQuery("#barBottomContainer").css("background-color")
                    }), "*");
                }
                if (message.request === "/invite") {
                    //Ajax request:
                    jQuery.ajax({
                        "url": STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/gameinvitation"),
                        "data": {
                            "attempt_id": '<?= htmlReady($attempt->getId()) ?>',
                            "module_id": '<?= htmlReady($module->getId()) ?>',
                            "module_game_id": message.parameter.vanillalm_game_id,
                            "parameter": message.parameter,
                            "max": message.max,
                            "seminar_id": '<?= htmlReady(Context::get()->id) ?>'
                        },
                        "type": "post"
                    });
                    document.getElementById("lernmodule_iframe").contentWindow.postMessage(JSON.stringify({
                        "secret": '<?= $framesecret ?>',
                        "request_id": message.request_id
                    }), "*");
                }
                <? if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) : ?>
                if (message.request === "/configs") {
                    STUDIP.Lernmodule.default_configs = message.default_configs;
                    if (!jQuery.isEmptyObject(STUDIP.Lernmodule.default_configs)) {
                        jQuery(".widget-links a.configure").show();
                    } else {
                        jQuery(".widget-links a.configure").hide();
                    }
                    document.getElementById("lernmodule_iframe").contentWindow.postMessage(JSON.stringify({
                        "secret": '<?= $framesecret ?>',
                        "request_id": message.request_id,
                        "configs": <?= json_encode(($coursemodule['customdata'] && $coursemodule['customdata']['configs']) ? $coursemodule['customdata']['configs']->getArrayCopy() : array()) ?>
                    }), "*");
                }
                <? endif ?>

            <? if (class_exists("Blubber")) : ?>
                if (message.request === "/postTimelineMessage") {
                    //show dialog that asks if user wants to share the message
                    if (STUDIP.Lernmodule.dont_blubber) {
                        STUDIP.Dialog.fromURL(
                            STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/blubber"),
                            {
                                "method": "POST",
                                "data": { "message": message.message }
                            }
                        );
                    }
                }
                <? endif ?>
            }
        }
    }, false);
</script>
<style>
    .widget-links a.configure {
        display: none;
    }
</style>
<iframe
    <? $url = $module->getStartURL($framesecret);
    if ($game_attendance) {
        $url = URLHelper::getURL($url, $game_attendance->game['parameter']->getArrayCopy(), true);
    }
    if ($lernmodulcourse['customdata']['configs']) {
        $url = URLHelper::getURL($url, $lernmodulcourse['customdata']['configs']->getArrayCopy(), true);
    }
    ?>
        src="<?= htmlReady($url) ?>"
    <?= $module['sandbox'] && (!$module['url'] || (parse_url($url, PHP_URL_HOST) === $_SERVER['SERVER_NAME'])) ? " sandbox=\"". implode(" ", $sandbox)."\"" : "" ?>
        style="width: 100%; height: 90vh; border: none;"
        id="lernmodule_iframe"
></iframe>