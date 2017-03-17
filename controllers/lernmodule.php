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
        $this->utf8decode_xhr = false;
    }

    public function overview_action()
    {
        Navigation::activateItem("/course/lernmodule/overview");
        LernmodulAttempt::cleanUpDatabase();
        $this->module = Lernmodul::findByCourse($_SESSION['SessionSeminar']);

        if (Request::option("quit")) {
            $attendance = new LernmodulGameAttendance(Request::option("quit"));
            if ($attendance['user_id'] === $GLOBALS['user']->id) {
                $attendance->delete();
                $this->redirect("lernmodule/overview");
            }
        }

        $statement = DBManager::get()->prepare("
            SELECT lernmodule_game_attendances.*
            FROM lernmodule_game_attendances
                INNER JOIN lernmodule_games ON (lernmodule_game_attendances.game_id = lernmodule_games.game_id)
                INNER JOIN lernmodule_courses ON (lernmodule_games.module_id = lernmodule_courses.module_id)
            WHERE lernmodule_courses.seminar_id = :course_id
                AND lernmodule_game_attendances.user_id = :user_id
        ");
        $statement->execute(array(
            'course_id' => $_SESSION['SessionSeminar'],
            'user_id' => $GLOBALS['user']->id
        ));
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $attendance = LernmodulGameAttendance::buildExisting($data);
            PageLayout::postMessage(
                MessageBox::info(
                    sprintf(
                        _("Sie haben zuletzt an '%s' teilgenommen."),
                        htmlReady($attendance->game->module['name'])
                    ),
                    array(
                        '<a href="' . PluginEngine::getLink($this->plugin, array(), "lernmodule/gameparticipation/" . $attendance->game->getId()) . '">' . _("Wieder einsteigen") . '</a>',
                        '<a href="'. PluginEngine::getLink($this->plugin, array('quit' => $attendance->getId()), "lernmodule/overview") .'">'._("Teilnahme beenden").'</a>'
                    )
                )
            );
        }
        foreach (LernmodulGame::findOpenGames($_SESSION['SessionSeminar']) as $opengame) {
            if (!$opengame->participates()) {
                PageLayout::postMessage(
                    MessageBox::info(
                        sprintf(
                            _("%s sucht noch weitere Teilnehmer für '%s'."),
                            get_fullname($opengame['user_id']),
                            htmlReady($opengame->module['name'])
                        ),
                        array(
                            '<a href="' . PluginEngine::getLink($this->plugin, array(), "lernmodule/gameparticipation/" . $opengame->getId()) . '">' . _("Einladung annehmen") . '</a>'
                        )
                    )
                );
            }
        }
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
        $this->attempt = new LernmodulAttempt();
        $this->attempt->setData(array(
            'user_id' => $course_connection['anonymous_attempts'] ? null : $GLOBALS['user']->id,
            'module_id' => $module_id
        ));
        $this->attempt->store();
        if (Request::option("attendance")) {
            $this->game_attendence = new LernmodulGameAttendance(Request::option("attendance"));
            if ($GLOBALS['user']->id !== $this->game_attendence['user_id']) {
                var_dump($GLOBALS['user']->id);
                var_dump($this->game_attendence['user_id']);
                var_dump($GLOBALS['user']->id !== $this->game_attendence['user_id']);
                die();

                $this->redirect("lernmodule/overview");
            }
        }
        LernmodulAttempt::cleanUpDatabase();
    }

    public function edit_action($module_id = null)
    {
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id ?: null);
        if ($this->module['type'] && !$this->module->isNew()) {
            $class = ucfirst($this->module['type'])."Lernmodul";
            $this->module = $class::buildExisting($this->module->toArray()); //toRawArray
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
                if ($this->module['material_id'] && !$this->module['url']) {
                    $material = new LernmarktplatzMaterial($this->module['material_id'] != 1 ? $this->module['material_id'] : null);

                    $material['name'] = $this->module['name'];
                    $material['filename'] = $this->module['name'].".zip";
                    $material['short_description'] = $this->module['name'];
                    $material['description'] = $this->module['name'];
                    $material['content_type'] = "application/zip";
                    $material['front_image_content_type'] = "application/zip";
                    $material['user_id'] = $GLOBALS['user']->id;
                    copy($_FILES['modulefile']['tmp_name'], $material->getFilePath());
                    $material->store();
                    //$topics = $material->getTopics();
                    $material->addTag("Lernmodul");
                    $material->pushDataToIndexServers();

                    $this->module['material_id'] = $material->getId();
                    $this->module->store();
                }
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
        $this->attempts = LernmodulAttempt::findbyCourseAndModule($_SESSION['SessionSeminar'], $this->module->getId());

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
        $this->attempt = new LernmodulAttempt($attempt_id);
        if ($this->attempt['user_id'] !== $GLOBALS['user']->id) {
            throw new AccessDeniedException();
        }
        if (Request::isPost()) {
            $this->attempt['chdate'] = time();
            $message = studip_utf8decode(Request::getArray("message"));
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
        $filename = $GLOBALS['TMP_PATH']."/".md5(uniqid()).".zip";
        create_zip_from_directory($this->module->getPath(), $filename);

        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"".$this->module['name'].".zip\"");
        header('Content-Length: ' . filesize($filename));
        header('Pragma: public');

        $this->render_nothing();

        readfile($filename);
        unlink($filename);
    }

    public function gameinvitation_action()
    {
        if (Request::isPost()) {
            $game = new LernmodulGame();
            $game['user_id'] = $GLOBALS['user']->id;
            $game['seminar_id'] = Request::option("seminar_id");
            $game['module_id'] = Request::option("module_id");
            $game['max_players'] = Request::int("max") + 1;
            $game['parameter'] = Request::getArray("parameter");
            $game['closed'] = 0;
            $game->store();

            $game_attendence = new LernmodulGameAttendance();
            $game_attendence['game_id'] = $game->getId();
            $game_attendence['user_id'] = $GLOBALS['user']->id;
            $game_attendence->store();
        }
        $this->render_text("ok");
    }

    public function gameparticipation_action($game_id)
    {
        $game = new LernmodulGame($game_id);
        if (!$GLOBALS['perm']->have_studip_perm("autor", $game['seminar_id'])) {
            throw new AccessDeniedException();
        }
        $game_attendence = LernmodulGameAttendance::findOneBySQL("game_id = ? AND user_id = ?", array(
            $game->getId(),
            $GLOBALS['user']->id
        ));
        if (!$game_attendence) {
            $game_attendence = new LernmodulGameAttendance();
            $game_attendence['game_id'] = $game->getId();
            $game_attendence['user_id'] = $GLOBALS['user']->id;
            $game_attendence->store();
        }
        $this->redirect("lernmodule/view/".$game['module_id']."?cid=".$game['seminar_id']."&attendance=".$game_attendence->getId());
    }

    public function blubber_action()
    {
        if (Request::submitted("save") && Request::isPost()) {

        }
    }

}