
<iframe src="<?= URLHelper::getLink("plugins.php/lernmoduleplugin/h5p/iframe/".$module->getId()) ?>"
        style="width: 100%; border: none; height: 100vh;"></iframe>

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