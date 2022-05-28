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
        $this->block_id = Request::get('block_id');
        $this->javascript_global_variables = [
            'block_id' => $this->block_id,
            'module_id' => $module_id,
            'moduleContents' => json_decode($this->mod['customdata']),
            'saveRoute' => $this->url_for('vuejseditor/save')
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
        if (!$module_id) {
            throw new Exception(_("'module_id' fehlt"));
        }
        if (!$task_definition) {
            throw new Exception(_("'task_definition' fehlt"));
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
            $connection->store();
        }
        if ($this->mod['draft']) {
            $this->mod['draft'] = 0;
        }
        $this->mod->customdata = $task_definition;
        $this->mod->store();
        $this->render_json([
            'status' => 'success',
            'taskDefinition' => json_decode($task_definition),
        ]);
    }

}