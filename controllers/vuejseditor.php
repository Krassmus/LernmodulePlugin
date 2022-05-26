<?php

class VuejseditorController extends PluginController
{
    public function edit_action($module_id = null)
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm(
                "tutor",
                Context::get()->id
            )) {
            throw new AccessDeniedException();
        }
        if (!$module_id) {
            $this->mod = new VuejsLernmodul();
            $this->mod['draft'] = 1;
            $this->mod['type'] = "vuejs";
            $this->mod->store();
            $this->redirect(
                PluginEngine::getURL(
                    $this->plugin,
                    array('block_id' => Request::option("block_id")),
                    "vuejseditor/edit/" . $this->mod->getId()
                )
            );
            return;
        }
        Navigation::activateItem("/course/lernmodule/overview");
        $this->mod = VuejsLernmodul::find($module_id);

        $styles = array(
            $this->plugin->getPluginURL() . '/assets/vuejs/vuejseditor.css',
        );
        $scripts = array(
            $this->plugin->getPluginURL() . '/assets/vuejs/vuejseditor.js',
        );

        foreach ($styles as $style) {
            PageLayout::addStylesheet($style);
        }
        foreach ($scripts as $script) {
            PageLayout::addScript($script);
        }

        if ($this->mod['draft']) {
            PageLayout::setTitle(_("Neues Lernmodul erstellen"));
        } else {
            PageLayout::setTitle(_("Lernmodul bearbeiten"));
        }
    }

    public function save_action()
    {
        if (!Request::isPost()) {
            throw new Exception("POST-only route");
        }
        $module_id = Request::get('module_id');
        if (!$module_id) {
            throw new Exception("'module_id' missing");
        }
        CSRFProtection::verifySecurityToken();
        $this->mod = VuejsLernmodul::find($module_id);
        $connection = $this->mod->courseConnection(Context::get()->id);
        if ($connection->isNew()) {
            $block = LernmodulBlock::find(Request::option("block_id"));
            $connection['block_id'] = Request::option("block_id");
            $connection['position'] = count($block->coursemodules) + 0;
            $connection->store();
        }
        if ($this->mod['draft']) {
            $this->mod['draft'] = -1;
            $this->mod->store();
        }
        if ($this->mod) {
            PageLayout::postSuccess(_("Lernmodul wurde gespeichert"));
            $this->redirect(
                PluginEngine::getURL(
                    $this->plugin,
                    array(),
                    "lernmodule/view/" . $this->mod->getId()
                )
            );
        } else {
            PageLayout::postError(
                _("Lernmodul konnte nicht gespeichert werden. Daten unvollständig.")
            );
            $this->redirect(
                PluginEngine::getURL(
                    $this->plugin,
                    array(),
                    "vuejseditor/edit/" . $this->mod->getId()
                )
            );
        }
    }

}