<?php

class LernmoduleController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::getItem("/course/lernmodule")->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info")
                : Assets::image_path("icons/black/20/learnmodule")
        );
        PageLayout::setTitle($GLOBALS['SessSemName']["header_line"]." - ".$this->plugin->getDisplayTitle());
    }

    public function overview_action()
    {
        Navigation::activateItem("/course/lernmodule/overview");
        LernmodulVersuch::cleanUpDatabase();
        $this->module = Lernmodul::findBySQL("seminar_id = ? ORDER BY name ASC", array($_SESSION['SessionSeminar']));
    }

    public function view_action($module_id)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->attempt = new LernmodulVersuch();
        $this->attempt->setData(array(
            'user_id' => $GLOBALS['user']->id,
            'module_id' => $module_id
        ));
        $this->attempt->store();
        LernmodulVersuch::cleanUpDatabase();
        $this->mod = new Lernmodul($module_id);
    }

    public function edit_action($module_id = null)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id);
        $this->lernmodule = Lernmodul::findBySQL("seminar_id = ? AND module_id != ? ORDER BY name ASC" , array(
            $_SESSION['SessionSeminar'],
            $module_id
        ));
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
            $this->module->setDependencies(Request::getArray("dependencies"), $_SESSION['SessionSeminar']);
            if ($_FILES['modulefile']['size'] > 0) {
                $this->module->copyModule($_FILES['modulefile']['tmp_name']);
            }
            PageLayout::postMessage(MessageBox::success(_("Lernmodul erfolgreich gespeichert.")));
            $this->redirect("lernmodule/overview");
        }
    }

    public function delete_action($module_id)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id);
        if (Request::isPost()) {
            $this->module->delete();
            PageLayout::postMessage(MessageBox::success(_("Lernmodul gelöscht.")));
        }
        $this->redirect("lernmodule/overview");
    }

    public function update_attempt_action($attempt_id)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->attempt = new LernmodulVersuch($attempt_id);
        if ($this->attempt['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $this->attempt['chdate'] = time();
            if ($this->attempt['chdate'] > $this->attempt['mkdate'] + 29) {
                $this->attempt['successful'] = 1;
            }
            $this->attempt->store();
        }
        $this->render_nothing();
    }

    public function ranking_action()
    {
        Navigation::activateItem("/course/lernmodule/ranking");
        $this->module = Lernmodul::findBySQL("seminar_id = ? ORDER BY name ASC", array($_SESSION['SessionSeminar']));
        $this->students = Course::findCurrent()->members->filter(function ($student) { return $student['status'] === "autor"; });
    }
}