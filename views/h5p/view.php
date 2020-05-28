
<div id="fullscreen_container">
    <iframe src="<?= URLHelper::getLink("plugins.php/lernmoduleplugin/h5p/iframe/".$module->getId(), array('a' => $attempt->getId())) ?>"
            style="width: 100%; border: none; height: 100vh; min-height: 721px;"></iframe>
</div>

<script>
    STUDIP.Lernmodule = {
        requestFullscreen: function () {
            var module = jQuery("#fullscreen_container")[0];
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
    <? if ($module) : ?>
        jQuery(function () {
            window.setTimeout(function () {
                if (!STUDIP.Lernmodule.received_message_api_messages) {
                    jQuery.post(
                        STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>"),
                        {"message": {"success": 1}}
                    );
                    window.setInterval(function () {
                        jQuery.post(
                            STUDIP.URLHelper.getURL(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>"),
                            {"message": {"success": 1}}
                        );
                    }, 10 * 1000);
                }
            }, <?= (integer) Config::get()->LERNMODUL_SECONDS_TO_SUCCESS ?> * 1000);
        });
    <? endif ?>
</script>
