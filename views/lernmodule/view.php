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


<iframe
        sandbox="<?= implode(" ", $sandbox) ?>"
        src="<?= htmlReady($plugin->getPluginURL()."/moduledata/".$mod->getId()."/".($mod['start_file'] ?: "index.html")) ?>"
        style="width: 100%; height: 90vh; border: none;"

></iframe>