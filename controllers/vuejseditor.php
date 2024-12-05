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
            $this->mod['name'] = "Neues Stud.IP H5P-Lernmodul";
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
        $this->block_id = Request::get('block_id');
        $connection = $this->mod->courseConnection(Context::get()->id);
        $this->javascript_global_variables = [
            'block_id' => $this->block_id,
            'module' => [
                'customdata' => json_decode($this->mod['customdata']),
                'module_id' => $this->mod['id'],
                'name' => $this->mod['name']
            ],
            'infotext' => $connection['infotext'] ?? '',
            'saveRoute' => $this->url_for('vuejseditor/save'),
            'LERNMODULE_DEBUG' => Config::get()->LERNMODULE_DEBUG,
            'LERNMODULE_PREVIEW' => Config::get()->LERNMODULE_PREVIEW,
            'LERNMODULE_LAZYLOADING' => Config::get()->LERNMODULE_LAZYLOADING,
        ];

        if ($this->mod['draft']) {
            PageLayout::setTitle(_("Neues Lernmodul erstellen"));
        } else {
            PageLayout::setTitle(_("Lernmodul bearbeiten"));
        }
    }

    public function save_action()
    {
        CSRFProtection::verifySecurityToken();
        if (!Request::isPost()) {
            throw new Exception("POST-only route");
        }
        $module_id = Request::option('module_id');
        $task_definition = Request::get('task_definition');
        $name = Request::get('name');
        $infotext = Request::get('infotext');
        if (!isset($module_id)) {
            throw new Exception(_("'module_id' fehlt"));
        }
        if (!isset($task_definition)) {
            throw new Exception(_("'task_definition' fehlt"));
        }
        if (!isset($name)) {
            throw new Exception(_("'name' fehlt"));
        }
        if (!isset($infotext)) {
            throw new Exception(_("'infotext' fehlt"));
        }
        $this->mod = VuejsLernmodul::find($module_id);
        if (!$this->mod) {
            throw new Exception(_("Lernmodul nicht gefunden."));
        }
        $connection = $this->mod->courseConnection(Context::get()->id);
        if ($connection->isNew()) {
            $block = LernmodulBlock::find(Request::option("block_id"));
            if (!$block) {
                throw new Exception(_('Block nicht gefunden.'));
            }
            $connection['block_id'] = Request::option("block_id");
            $connection['position'] = count($block->coursemodules) + 0;
        }
        if ($this->mod['draft']) {
            $this->mod['draft'] = 0;
        }
        $connection['infotext'] = $infotext;
        $connection->store();
        $this->mod->customdata = $task_definition;
        $this->mod->name = $name;
        $this->mod->store();
        $this->render_json([
            'status' => 'success',
            'taskDefinition' => json_decode($task_definition),
            'moduleName' => $name,
            'infotext' => $infotext
        ]);
    }
}
