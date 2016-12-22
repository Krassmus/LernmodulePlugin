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
        <?= $module['sandbox'] ? " sandbox=\"". implode(" ", $sandbox)."\"" : "" ?>
        src="<?= htmlReady($module->getStartURL()) ?>"
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
    window.setInterval(function () {
        jQuery.post(
            STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>",
            {"success" : 1}
        );
    }, 30 * 1000);
    <? endif ?>
    window.addEventListener("message", function (event) {
        var origin = event.origin || event.originalEvent.origin;
        var message = JSON.parse(event.data);
        if (message.secret === '<?= $framesecret ?>') {
            //it's from the correct window
            jQuery.post(
                STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>",
                message
            );
        }
    }, false);
</script>
