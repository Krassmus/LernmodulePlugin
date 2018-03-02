<?php

class EditorController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
    }

    public function metadata_action($module_id = null) {
        if (Request::isPost() && Request::get("name")) {
            $this->module = new Lernmodul($module_id ?: null);
            $this->module['name'] = Request::get("name");
            $this->module['user_id'] = $GLOBALS['user']->id;
            $this->module['type'] = "html";
            $this->module['sandbox'] = 1;
            $this->module->store();

            $this->module = new VanillalmLernmodul($this->module->getId());

            $path = $this->module->getPath();
            if ($_FILES['logo'] && $_FILES['logo']['size']) {
                if (!file_exists($path)) {
                    mkdir($path);
                }
                mkdir($path."/images");
                copy($_FILES['logo']['tmp_name'], $path."/images/logo.jpg");
                $this->module['image'] = "images/logo.jpg";
                $this->module->store();

                $this->module->init();
            }
            $connection = $this->module->courseConnection(Context::getId());
            $connection['module_id'] = $this->module->getId();
            $connection['seminar_id'] = Context::getId();
            $connection->store();

            PageLayout::postSuccess(_("Lernmodul wurde erstellt und ist bereit zum Bearbeiten."));
            $this->redirect("editor/block/".$this->module->getId());
        }

    }

    public function block_action($module_id, $block_id = null) {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $this->module = new VanillalmLernmodul($module_id);
        $this->module->init();
        if (!$block_id) {

        }
    }

}