<? $sandbox = array(
    "allow-forms",
    "allow-modals",
    "allow-orientation-lock",
    "allow-pointer-lock",
    "allow-popups",
    //"allow-popups-to-escape-sandbox",
    "allow-presentation",
    //"allow-same-origin",
    "allow-scripts",
    //"allow-top-navigation",
) ?>

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
        }
    };
</script>

<? $framesecret = md5(uniqid()) ?>

<iframe
        <? $url = $module->getStartURL($framesecret) ?>
        src="<?= htmlReady($url) ?>"
        <?= $module['sandbox'] && (!$module['url'] || (parse_url($url, PHP_URL_HOST) === $_SERVER['SERVER_NAME'])) ? " sandbox=\"". implode(" ", $sandbox)."\"" : "" ?>
        style="width: 100%; height: 90vh; border: none;"
        id="lernmodule_iframe"
></iframe>


<script>
    <? if ($module['end_file']) : ?>
    var end_file_found = false;
    window.setInterval(function () {
        if (!end_file_found) {
            //search for iframe-page
            var page = document.getElementById("lernmodule_iframe").contentWindow.location.href;
            var end_file = "<?= htmlReady($module['end_file']) ?>";
            page = page.split("?")[0];
            if (page.indexOf(end_file) !== -1) {
                end_file_found = true;
                jQuery.post(
                    STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>",
                    {"success" : 1}
                );
            }
        }
    }, 500);
    <? else : ?>
    window.setTimeout(function () {
        if (!STUDIP.Lernmodule.received_message_api_messages) {
            jQuery.post(
                STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>",
                {"success": 1}
            );
        }
    }, 30 * 1000);
    STUDIP.Lernmodule.received_message_api_messages = false;
    <? endif ?>
    window.addEventListener("message", function (event) {
        var origin = event.origin || event.originalEvent.origin;
        var myorigin = "<?= htmlReady($myorigin) ?>";
        var message = JSON.parse(event.data);
        if (message.secret === '<?= $framesecret ?>') {
            STUDIP.Lernmodule.received_message_api_messages = true;
            //it's from the correct window, yay!
            delete message.secret;
            jQuery.post(
                STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>",
                { "message": message }
            );
        }
    }, false);
</script>
