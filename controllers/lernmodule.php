<?php

class LernmoduleController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info")
                : Assets::image_path("icons/black/20/learnmodule")
        );
        PageLayout::setTitle($GLOBALS['SessSemName']["header_line"]." - ".$this->plugin->getDisplayTitle());
    }

    public function overview_action()
    {
        $this->module = Lernmodul::findBySeminar_id($_SESSION['SessionSeminar']);
    }

    public function view_action($module_id)
    {
        $this->mod = new Lernmodul($module_id);
    }

    public function edit_action($module_id = null)
    {
        $this->module = new Lernmodul($module_id);
        PageLayout::setTitle($this->module->isNew() ? _("Lernmodul erstellen") : _("Lernmodul bearbeiten"));
        if (Request::isPost()) {
            $data = Request::getArray("module");
            if (LernmodulePlugin::mayEditSandbox()) {
                $data['sandbox'] = (int) $data['sandbox'];
            } else {
                unset($data['sandbox']);
            }
            $this->module->setData($data);
            $this->module['seminar_id'] = $_SESSION['SessionSeminar'];
            $this->module['user_id'] = $GLOBALS['user']->id;
            $this->module->store();
            if ($_FILES['modulefile']['size'] > 0) {
                $this->module->copyModule($_FILES['modulefile']['tmp_name']);
            }
            PageLayout::postMessage(MessageBox::success(_("Lernmodul erfolgreich gespeichert.")));
            $this->redirect("lernmodule/overview");
        }
    }

    public function delete_action($module_id)
    {
        $this->module = new Lernmodul($module_id);
        if (Request::isPost()) {
            $this->module->delete();
            PageLayout::postMessage(MessageBox::success(_("Lernmodul gelöscht.")));
        }
        $this->redirect("lernmodule/overview");
    }
}