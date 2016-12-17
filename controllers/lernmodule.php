<?php

class LernmoduleController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::getItem("/course/lernmodule")->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info")
                : Assets::image_path("icons/black/16/learnmodule")
        );
        PageLayout::setTitle($GLOBALS['SessSemName']["header_line"]." - ".$this->plugin->getDisplayTitle());
    }

    public function overview_action()
    {
        Navigation::activateItem("/course/lernmodule/overview");
        LernmodulVersuch::cleanUpDatabase();
        $this->module = Lernmodul::findByCourse($_SESSION['SessionSeminar']);
    }

    public function view_action($module_id)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->mod = new HtmlLernmodul($module_id);
        $course_connection = $this->mod->courseConnection($_SESSION['SessionSeminar']);
        $this->attempt = new LernmodulVersuch();
        $this->attempt->setData(array(
            'user_id' => $course_connection['anonymous_attempts'] ? null : $GLOBALS['user']->id,
            'module_id' => $module_id
        ));
        $this->attempt->store();
        LernmodulVersuch::cleanUpDatabase();
        if (!file_exists($this->mod->getPath())) {
            PageLayout::postMessage(MessageBox::error(_("Kann Lernmodul nicht finden.")));
        }
    }

    public function edit_action($module_id = null)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id);
        if ($this->module['type']) {
            $class = ucfirst($this->module['type'])."Lernmodul";
            $this->module = $class::buildExisting($this->module->toRawArray());
        }
        $this->lernmodule = Lernmodul::findBySQL("INNER JOIN lernmodule_courses USING (module_id) 
                WHERE lernmodule_courses.seminar_id = ? 
                    AND module_id != ? 
                    AND lernmodule_courses.anonymous_attempts = '0' 
                ORDER BY name ASC" , array(
            $_SESSION['SessionSeminar'],
            $module_id
        ));
        $this->modulecourse = new LernmodulCourse(array($module_id, $_SESSION['SessionSeminar']));
        PageLayout::setTitle($this->module->isNew() ? _("Lernmodul erstellen") : _("Lernmodul bearbeiten"));
        if (Request::isPost()) {
            $data = Request::getArray("module");
            if (!$data['name']) {
                PageLayout::postMessage(MessageBox::error(_("Datei ist leider zu groß.")));
                $this->redirect("lernmodule/overview");
                return;
            }
            if (LernmodulePlugin::mayEditSandbox()) {
                $data['sandbox'] = (int) $data['sandbox'];
            } else {
                unset($data['sandbox']);
            }
            $this->module->setData($data);
            $this->module['user_id'] = $GLOBALS['user']->id;
            $this->module->store();

            $this->modulecourse['module_id'] = $this->module->getId();
            $this->modulecourse['seminar_id'] = $_SESSION['SessionSeminar'];
            $data = Request::getArray("module");
            $data['anonymous_attempts'] = (int) $data['anonymous_attempts'];
            $this->modulecourse->setData($data);
            $this->modulecourse->store();

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
            $this->attempt['successful'] = 1;
            $this->attempt->store();
        }
        $this->render_nothing();
    }

    public function ranking_action()
    {
        Navigation::activateItem("/course/lernmodule/ranking");
        $this->module = Lernmodul::findBySQL("INNER JOIN lernmodule_courses USING (module_id) WHERE lernmodule_courses.seminar_id = ? ORDER BY name ASC", array($_SESSION['SessionSeminar']));
        $this->students = Course::findCurrent()->members->filter(function ($student) { return $student['status'] === "autor"; });
    }
}