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
        $this->mod = new Lernmodul($module_id);
        $class = ucfirst($this->mod['type'])."Lernmodul";
        $this->mod = $class::buildExisting($this->mod->toRawArray());
        if (!$this->mod['url'] && !file_exists($this->mod->getPath())) {
            PageLayout::postMessage(MessageBox::error(_("Kann Lernmodul nicht finden.")));
        }
        
        $course_connection = $this->mod->courseConnection($_SESSION['SessionSeminar']);
        $this->attempt = new LernmodulVersuch();
        $this->attempt->setData(array(
            'user_id' => $course_connection['anonymous_attempts'] ? null : $GLOBALS['user']->id,
            'module_id' => $module_id
        ));
        $this->attempt->store();
        LernmodulVersuch::cleanUpDatabase();
    }

    public function edit_action($module_id = null)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id ?: null);
        if ($this->module['type'] && !$this->module->isNew()) {
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
        if ($module_id) {
            $this->modulecourse = LernmodulCourse::find(array($module_id, $_SESSION['SessionSeminar']));
        }
        PageLayout::setTitle($this->module->isNew() ? _("Lernmodul erstellen") : _("Lernmodul bearbeiten"));
        if (Request::isPost()) {
            $data = Request::getArray("module");
            if (!$data['name']) { //die Variable name passte nicht mehr in den Request und fehlt daher
                PageLayout::postMessage(MessageBox::error(_("Datei ist leider zu groß.")));
                $this->redirect("lernmodule/overview");
                return;
            }
            if (!LernmodulePlugin::mayEditSandbox()) {
                unset($data['sandbox']);
            }
            $this->module->setData($data);
            if ($this->module['url'] && !$this->module['image']) {
                $og = OpenGraphURL::fromURL($this->module['url']);
                $og->fetch();
                if ($og['image']) {
                    $this->module['image'] = $og['image'];
                }
            }
            $this->module['user_id'] = $GLOBALS['user']->id;
            $this->module->store();

            if (!$this->module->getId()) {
                PageLayout::postMessage(MessageBox::error(_("Konnte Lernmodul nicht speichern.")));
                $this->redirect("lernmodule/overview");
                return;
            }

            if (!$this->modulecourse) {
                $this->modulecourse = new LernmodulCourse();
                $this->modulecourse['module_id'] = $this->module->getId();
                $this->modulecourse['seminar_id'] = $_SESSION['SessionSeminar'];
            }
            $modulecoursedata = Request::getArray("modulecourse");
            $modulecoursedata['starttime'] = strtotime($modulecoursedata['starttime']) ?: null;
            $this->modulecourse->setData($modulecoursedata);
            $this->modulecourse->store();

            $success = true;

            $this->module->setDependencies(Request::getArray("dependencies"), $_SESSION['SessionSeminar']);
            if ($_FILES['modulefile']['size'] > 0) {
                $success = $this->module->copyModule($_FILES['modulefile']['tmp_name']);
            }
            if ($success) {
                PageLayout::postMessage(MessageBox::success(_("Lernmodul erfolgreich gespeichert.")));
            }
            $this->redirect("lernmodule/overview");
        }
    }

    public function evaluation_action($module_id = null)
    {
        PageLayout::addScript("jquery/jquery.tablesorter-2.22.5.js");
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id ?: null);
        if ($this->module['type'] && !$this->module->isNew()) {
            $class = ucfirst($this->module['type'])."Lernmodul";
            $this->module = $class::buildExisting($this->module->toRawArray());
        }
        $this->attempts = LernmodulVersuch::findbyCourseAndModule($_SESSION['SessionSeminar'], $this->module->getId());

        $this->data = array();
        $this->resultrows = array();
        foreach ($this->attempts as $attempt) {
            if ($attempt['successful']) {
                $line = array(
                    'studip_user_id' => $attempt['user_id'],
                    'studip_duration' => $attempt['chdate'] - $attempt['mkdate']
                );
                foreach ((array) $this->module->evaluateAttempt($attempt) as $index => $value) {
                    if (!isset($line[$index])) {
                        $line[$index] = $value;
                    }
                    if (!in_array($index, $this->resultrows)) {
                        $this->resultrows[] = $index;
                    }
                }
                $this->data[] = $line;
            }
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
            $message = Request::getArray("message");
            if ($message['success']) {
                $this->attempt['successful'] = 1;
            }
            unset($message['success']);
            $this->attempt->customdata = $message;
            $this->attempt->store();
        }
        $this->render_nothing();
    }

    public function download_action($module_id)
    {
        $this->module = new Lernmodul($module_id);
        $filename = $GLOBALS['TMP_PATH']."/".md5(uniqid());
        create_zip_from_directory($this->module->getPath(), $filename);
        header("Content-Type: application/zip");
        header("Content-Disposition: attachement; filename=\"".$this->module['name'].".zip\"");
        echo file_get_contents($filename);
        @unlink($filename);
        $this->render_nothing();
    }

}