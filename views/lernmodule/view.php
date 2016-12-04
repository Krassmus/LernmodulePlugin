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

<div style="text-align: center;">
    <?= \Studip\LinkButton::create(_("Vollbild"), "#", array('onClick' => "STUDIP.Lernmodule.requestFullscreen(); return false;")) ?>
</div>

<iframe
        <?= $mod['sandbox'] ? " sandbox=\"". implode(" ", $sandbox)."\"" : "" ?>
        src="<?= htmlReady($plugin->getPluginURL()."/moduledata/".$mod->getId()."/".($mod['start_file'] ?: "index.html")) ?>"
        style="width: 100%; height: 90vh; border: none;"
></iframe>

<script>
    window.setTimeout(function () {
        jQuery.post(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>");
    }, 1000 * 30);
</script>