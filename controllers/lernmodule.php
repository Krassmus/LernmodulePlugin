<?php

class LernmoduleController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(Icon::create("learnmodule", "info"));
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
            $this->module->setData(Request::getArray("module"));
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
}