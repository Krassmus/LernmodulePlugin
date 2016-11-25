
<iframe sandbox="allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-presentation allow-scripts"
        src="<?= htmlReady($plugin->getPluginURL()."/moduledata/".$mod->getId()."/".($mod['start_file'] ?: "index.html")) ?>"
        style="width: 100%; height: 90vh; border: none;"

></iframe>