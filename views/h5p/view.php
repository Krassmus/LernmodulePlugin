<? $sandbox = array(
    "allow-forms",
    "allow-modals",
    "allow-orientation-lock",
    "allow-pointer-lock",
    "allow-popups",
    //"allow-popups-to-escape-sandbox",
    "allow-presentation",
    "allow-same-origin", //we need this for ajax calls, which is a xss-vulnurability
    "allow-scripts",
    //"allow-top-navigation",
) ?>

<iframe src="<?= URLHelper::getLink("plugins.php/lernmoduleplugin/h5p/iframe/".$module->getId()) ?>"
        style="width: 100%; border: none; height: 100vh;" sandbox="<?= implode(" ", $sandbox) ?>"></iframe>

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