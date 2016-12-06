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
        id="lernmodule_iframe"
></iframe>

<? if (!$mod['end_file']) : ?>
    <script>
        window.setTimeout(function () {
            jQuery.post(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>");
        }, 1000 * 30);
    </script>
<? else : ?>
    <script>
        var end_file_found = false;
        window.setInterval(function () {
            if (!end_file_found) {
                //search for iframe-page
                var page = document.getElementById("lernmodule_iframe").contentWindow.location.href;
                var end_file = "<?= htmlReady($mod['end_file']) ?>";
                page = page.split("?")[0];
                if (page.indexOf(end_file) !== -1) {
                    end_file_found = true;
                    jQuery.post(STUDIP.ABSOLUTE_URI_STUDIP + "plugins.php/lernmoduleplugin/lernmodule/update_attempt/<?= htmlReady($attempt->getId()) ?>");
                }
            }
        }, 500);
    </script>
<? endif ?>