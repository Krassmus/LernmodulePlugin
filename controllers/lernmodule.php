<?php

class LernmoduleController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (Navigation::hasItem("/course/lernmodule")) {
            Navigation::getItem("/course/lernmodule")->setImage(
                Icon::create("learnmodule", "info")
            );
        }
        PageLayout::setTitle(Context::getHeaderLine()." - ".$this->plugin->getDisplayTitle());
        $this->course_id = Context::get()->id;
    }

    public function overview_action()
    {
        Navigation::activateItem("/course/lernmodule/overview");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/lernmoduleplugin.js");
        Lernmodul::deleteBySQL("draft = '1' AND mkdate < UNIX_TIMESTAMP() - 86400");
        $this->module = Lernmodul::findByCourse($this->course_id);
        $this->settings = new LernmodulCourseSettings(Context::get()->id);
        $this->blocks = LernmodulBlock::findBySQL("seminar_id = ? ORDER BY position ASC", [$this->course_id]);
        if (!count($this->blocks)) {
            $block = new LernmodulBlock();
            $block['seminar_id'] = $this->course_id;
            $block->store();
            $this->blocks[] = $block;
        }


        if (Request::option("quit")) {
            $attendance = new LernmodulGameAttendance(Request::option("quit"));
            if ($attendance['user_id'] === $GLOBALS['user']->id) {
                $attendance->delete();
                $this->redirect("lernmodule/overview");
                return;
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
            'course_id' => $this->course_id,
            'user_id' => $GLOBALS['user']->id
        ));
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $attendance = LernmodulGameAttendance::buildExisting($data);
            PageLayout::postMessage(
                MessageBox::info(
                    sprintf(
                        dgettext("lernmoduleplugin","Sie haben zuletzt an '%s' teilgenommen."),
                        htmlReady($attendance->game->module['name'])
                    ),
                    array(
                        '<a href="' . PluginEngine::getLink($this->plugin, array(), "lernmodule/gameparticipation/" . $attendance->game->getId()) . '">' . dgettext("lernmoduleplugin","Wieder einsteigen") . '</a>',
                        '<a href="'. PluginEngine::getLink($this->plugin, array('quit' => $attendance->getId()), "lernmodule/overview") .'">'.dgettext("lernmoduleplugin","Teilnahme beenden").'</a>'
                    )
                )
            );
        }
        foreach (LernmodulGame::findOpenGames($this->course_id) as $opengame) {
            if (!$opengame->participates()) {
                PageLayout::postMessage(
                    MessageBox::info(
                        sprintf(
                            dgettext("lernmoduleplugin","%s sucht noch weitere Teilnehmer für '%s'."),
                            get_fullname($opengame['user_id']),
                            htmlReady($opengame->module['name'])
                        ),
                        array(
                            '<a href="' . PluginEngine::getLink($this->plugin, array(), "lernmodule/gameparticipation/" . $opengame->getId()) . '">' . dgettext("lernmoduleplugin","Einladung annehmen") . '</a>'
                        )
                    )
                );
            }
        }
    }

    public function view_action($module_id)
    {
        $this->mod = new Lernmodul($module_id);
        PageLayout::setTitle($this->mod['name']);
        if (Context::get()->id) {
            Navigation::activateItem("/course/lernmodule/overview");
        } elseif ($this->mod['material_id']) {
            $this->set_layout(null);
        }

        $class = ucfirst($this->mod['type'])."Lernmodul";
        $this->mod = $class::buildExisting($this->mod->toArray());
        $isVuejsModule = $this->mod['type'] === 'vuejs'; // Vue.js Lernmodule don't have urls/paths.
        if (!$isVuejsModule && !$this->mod['url'] && !file_exists($this->mod->getPath())) {
            PageLayout::postMessage(MessageBox::error(dgettext("lernmoduleplugin", "Kann Lernmodul nicht finden.")));
        }

        $this->course_connection = $this->mod->courseConnection($this->course_id);
        $this->attempt = LernmodulAttempt::getByModule($this->mod->getId());
        if (Request::option("attendance")) {
            $this->game_attendence = new LernmodulGameAttendance(Request::option("attendance"));
            if ($GLOBALS['user']->id !== $this->game_attendence['user_id']) {
                PageLayout::postError(dgettext("lernmoduleplugin","IDs passen nicht zusammen. Beitritt verweigert."));
                $this->redirect("lernmodule/overview");
            }
        }
    }

    public function edit_action($module_id = null)
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id ?: null);
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/lernmoduleplugin.js");
        if ($this->module['type'] && !$this->module->isNew()) {
            $class = ucfirst($this->module['type'])."Lernmodul";
            $this->module = $class::buildExisting($this->module->toArray()); //toRawArray
        }
        $this->lernmodule = Lernmodul::findBySQL("INNER JOIN lernmodule_courses USING (module_id)
                WHERE lernmodule_courses.seminar_id = ?
                    AND module_id != ?
                    AND lernmodule_courses.anonymous_attempts = '0'
                ORDER BY name ASC" , array(
            $this->course_id,
            $module_id
        ));
        if ($module_id) {
            $this->modulecourse = LernmodulCourse::find(array($module_id, $this->course_id));
        }
        PageLayout::setTitle($this->module->isNew() ? dgettext("lernmoduleplugin","Lernmodul erstellen") : dgettext("lernmoduleplugin","Lernmodul bearbeiten"));
        if (Request::isPost()) {
            $data = Request::getArray("module");
            if (!$data['name']) { //die Variable name passte nicht mehr in den Request und fehlt daher
                PageLayout::postMessage(MessageBox::error(dgettext("lernmoduleplugin","Datei ist leider zu groß.")));
                $this->redirect("lernmodule/overview");
                return;
            }
            if (!LernmodulePlugin::mayEditSandbox()) {
                unset($data['sandbox']);
            }
            $this->module->setData($data);
            if ($this->module['url'] && !$this->module['image']) {
                $this->module['type'] = "html";
                $og = OpenGraphURL::fromURL($this->module['url']);
                $og->fetch();
                if ($og['image']) {
                    $this->module['image'] = $og['image'];
                }
            }
            $this->module['user_id'] = $GLOBALS['user']->id;
            $this->module->store();

            if (!$this->module->getId()) {
                PageLayout::postMessage(MessageBox::error(dgettext("lernmoduleplugin","Konnte Lernmodul nicht speichern.")));
                $this->redirect("lernmodule/overview");
                return;
            }

            if (!$this->modulecourse) {
                $this->modulecourse = new LernmodulCourse();
                $this->modulecourse['module_id'] = $this->module->getId();
                $this->modulecourse['seminar_id'] = $this->course_id;
                $this->modulecourse['block_id'] = Request::option("block_id");
            }
            $modulecoursedata = Request::getArray("modulecourse");
            $modulecoursedata['starttime'] = strtotime($modulecoursedata['starttime']) ?: null;
            $this->modulecourse->setData($modulecoursedata);
            if (!$this->modulecourse['gradebook_definition']) {
                $this->modulecourse['gradebook_definition'] = null;
            }
            $this->modulecourse->store();

            $success = true;

            $this->modulecourse->setDependencies(Request::getArray("dependencies"));
            if ($_FILES['modulefile']['size'] > 0) {
                $success = $this->module->copyModule($_FILES['modulefile']['tmp_name'], $_FILES['modulefile']['name']);
            }
            if ($success) {
                PageLayout::postMessage(MessageBox::success(dgettext("lernmoduleplugin","Lernmodul erfolgreich gespeichert.")));
            }
            $this->redirect("lernmodule/view/".$this->module->getId());
        }
        $statement = DBManager::get()->prepare("
            SELECT file_refs.id
            FROM file_refs
                INNER JOIN folders ON (folders.id = file_refs.folder_id)
                INNER JOIN files ON (files.id = file_refs.file_id)
            WHERE folders.range_id = :seminar_id
                AND folders.range_type = 'course'
                AND SUBSTRING(files.mime_type, 1, 6) = 'image/'
        ");
        $statement->execute(array('seminar_id' => $this->course_id));
        $this->course_images = FileRef::findMany($statement->fetchAll(PDO::FETCH_COLUMN, 0));
    }

    public function add_logo_action($module_id)
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $this->module = new Lernmodul($module_id);
        $relative_path = '';
        if (!empty($_FILES['logo'])) {
            $relative_path = "logo";
            $end = substr($_FILES['logo']['name'], strrpos($_FILES['logo']['name'], ".") + 1);
            $relative_path .= ".".$end;
            move_uploaded_file($_FILES['logo']['tmp_name'], $this->module->getPath() . "/" . $relative_path);
            $this->module['image'] = $relative_path;
            $this->module->store();
        }
        $this->render_text($this->module->getDataURL() . "/" . $relative_path);
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
        $this->attempts = LernmodulAttempt::findbyCourseAndModule($this->course_id, $this->module->getId());
        $this->course_connection = $this->module->courseConnection($this->course_id);
        if (!$this->course_connection['evaluation_for_students'] && !$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)) {
            throw new AccessDeniedException();
        }

        $this->data = array();
        $this->resultrows = array();
        foreach ($this->attempts as $attempt) {
            if ($attempt['successful']) {
                $line = array(
                    'studip_user_id' => $attempt['user_id'],
                    'studip_duration' => $attempt['chdate'] - $attempt['mkdate'],
                    'studip_mkdate' => $attempt['mkdate']
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
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/course/lernmodule/overview");
        $this->module = new Lernmodul($module_id);
        if (Request::isPost()) {
            $this->module->delete();
            PageLayout::postMessage(MessageBox::success(dgettext("lernmoduleplugin","Lernmodul gelöscht.")));
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
            $message = Request::getArray("message");
            if (Request::get('success') || $message['success']) {
                $this->attempt['successful'] = 1;
                echo "ok";
            }
            unset($message['success']);
            $old_message = $this->attempt->customdata
                ? $this->attempt->customdata->getArrayCopy()
                : array();
            $message['properties'] = array_merge((array)($old_message['properties'] ?? []), (array)($message['properties'] ?? []));
            if (!empty($old_message['points'])) {
                foreach ((array)$old_message['points'] as $class => $value) {
                    if ($message['points'][$class] < $value) {
                        $message['points'][$class] = $value;
                    }
                }
            }
            $this->attempt->customdata = $message;
            $this->attempt->store();

            $course_connection = new LernmodulCourse([$this->attempt['module_id'], Context::getId()]);
            if ($course_connection['gradebook_definition']) {
                $instance = \Grading\Instance::findOneBySQL("user_id = :user_id AND definition_id = :definition_id", array(
                    'user_id' => $GLOBALS['user']->id,
                    'definition_id' => $course_connection['gradebook_definition']
                ));
                if (!$instance) {
                    $instance = new \Grading\Instance();
                    $instance['user_id'] = $this->attempt['user_id'];
                    $instance['definition_id'] = $course_connection['gradebook_definition'];
                    $instance['rawgrade'] = 1;
                    if ($instance->isField('passed')) {
                        $instance['passed'] = 1;
                    }
                    $instance->store();
                } elseif ($course_connection['gradebook_rewrite']) {
                    $instance['rawgrade'] = 1;
                    if ($instance->isField('passed')) {
                        $instance['passed'] = 1;
                    }
                    $instance->store();
                }
            }

        }
        $this->render_nothing();
    }


    public function download_action($module_id)
    {
        $this->module = Lernmodul::find($module_id);
        $filename = $this->module->getExportFile();

        header('Content-Type: application/zip');
        header("Content-Disposition: attachment; filename=\"".$this->module['name'].".zip\"");
        header('Content-Length: ' . filesize($filename));
        header('Pragma: public');

        echo file_get_contents($filename);
        unlink($filename);
        die();
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

    }

    public function admin_action()
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $this->settings = new LernmodulCourseSettings(Context::get()->id);
        if ($this->settings->isNew()) {
            $this->settings->setId(Context::get()->id);
        }
        if (Request::option("delete_block")) {
            $block = new LernmodulBlock(Request::option("delete_block"));
            if ($block['seminar_id'] === Context::get()->id) {
                $block->delete();
                PageLayout::postSuccess(_("Block wurde gelöscht."));
            }
        }
        if (Request::isPost()) {
            $this->settings->setData(Request::getArray("data"));
            $this->settings->store();

            $blocks_data = Request::getArray("block");
            foreach (Request::getArray("blocks_order") as $position => $block_id) {
                $block = LernmodulBlock::find($block_id);
                if ($block && $block['seminar_id'] === Context::get()->id) {
                    $block['title'] = trim($blocks_data[$block_id]['title']) ?: null;
                    $block['infotext'] = trim(html_entity_decode(strip_tags($blocks_data[$block_id]['infotext']))) ? $blocks_data[$block_id]['infotext'] : null;
                    $block->store();
                }
            }
            if (Request::submitted("add_block")) {
                $block = new LernmodulBlock();
                $block['seminar_id'] = Context::get()->id;
                $block['position'] = LernmodulBlock::countBySQL("seminar_id = ? ORDER BY position ASC", [$this->course_id]) + 1;
                $block->store();
            }
            PageLayout::postSuccess(dgettext("lernmoduleplugin","Einstellungen wurden gespeichert."));
        }
        $this->blocks = LernmodulBlock::findBySQL("seminar_id = ? ORDER BY position ASC", [$this->course_id]);
    }

    public function sortblockmodules_action($block_id)
    {
        $block = new LernmodulBlock($block_id);
        $course_id = $block['seminar_id'];
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $course_id)) {
            throw new AccessDeniedException();
        }
        foreach (Request::getArray("order") as $position => $module_id) {
            $coursemodule = LernmodulCourse::find([$module_id, $course_id]);
            if (!$coursemodule) {
                $coursemodule = new LernmodulCourse();
                $coursemodule['module_id'] = $module_id;
                $coursemodule['seminar_id'] = $course_id;
            }
            $coursemodule['position'] = $position + 1;
            $coursemodule['block_id'] = $block_id;
            $coursemodule->store();
        }
        $this->render_text("ok");
    }

    public function sortblocks_action()
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        foreach (Request::getArray("order") as $position => $block_id) {
            $block = new LernmodulBlock($block_id);
            if ($block['seminar_id'] === Context::get()->id) {
                $block['position'] = $position + 1;
                $block->store();
            }
        }
        $this->render_text("ok");
    }

    /**
     * Dialog to select the way a lernmodule should be added (url, upload or h5p-editor (or OER-marketplace) (or Vue.js-Lernmodul)
     * @throws AccessDeniedException
     */
    public function add_action()
    {
        Navigation::activateItem("/course/lernmodule/overview");
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }

        $statement = DBManager::get()->prepare("
            SELECT lernmodule_h5plibs.*
            FROM lernmodule_h5plibs
            WHERE lernmodule_h5plibs.allowed = '1'
                AND lernmodule_h5plibs.runnable = '1'
                AND lib_id = (
                    SELECT l2.lib_id
                    FROM lernmodule_h5plibs AS l2
                    WHERE l2.name = lernmodule_h5plibs.name
                    ORDER BY major_version DESC, minor_version DESC
                    LIMIT 1
                )
            ORDER BY name ASC
        ");
        $statement->execute();
        $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->h5plibs = count($data);

        PageLayout::setTitle(_("Quelle des Lernmoduls auswählen"));
    }

    public function publish_action($module_id)
    {
        $this->module = Lernmodul::find($module_id);
        if (!class_exists("LernMarktplatz")) {
            throw new Exception("Lernmarktplatz ist nicht aktiviert.");
        }

        $image = $this->module['image']
            ? (preg_match("/^[a-f0-9]{32}$/", $this->module['image']) ? FileRef::find($this->module['image'])->file->getPath() : $this->module->getPath()."/".$this->module['image'])
            : "";
        $_SESSION['LernMarktplatz_CREATE_TEMPLATE'] = array(
            'name' => $this->module['name'],
            'module_id' => $module_id,
            'redirect_url' => PluginEngine::getURL($this->plugin, array('module_id' => $module_id), "lernmodule/after_marketplace_deployment"),
            'logo_tmp_file' => $image
        );
        if (!$this->module['url']) {
            $filename = $this->module->getExportFile();
            $_SESSION['LernMarktplatz_CREATE_TEMPLATE']['tmp_file'] = $filename;
            $_SESSION['LernMarktplatz_CREATE_TEMPLATE']['filename'] = $this->module['name'] . ($this->module['type'] === "h5p" ? ".h5p" : ".zip");
            $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $_SESSION['LernMarktplatz_CREATE_TEMPLATE']['player_url'] = PluginEngine::getURL($this->plugin, array(), "lernmodule/view/".$this->module->getId(), true);
            URLHelper::setBaseURL($oldbase);
        } else {
            $_SESSION['LernMarktplatz_CREATE_TEMPLATE']['player_url'] = $this->module['url'];
        }
        $this->redirect(URLHelper::getURL("plugins.php/lernmarktplatz/mymaterial/edit"));
    }

    public function after_marketplace_deployment_action()
    {
        $this->module = Lernmodul::find(Request::option("module_id"));
        if (!class_exists("LernMarktplatz")) {
            throw new Exception("Lernmarktplatz ist nicht aktiviert.");
        }
        if (Request::get("material_id")) {
            $this->module['material_id'] = Request::get("material_id");
            $this->module->store();
        }
        if (Request::get("url")) {
            $this->redirect(Request::get("url"));
        } else {
            $this->redirect(PluginEngine::getURL($this->plugin, array(), "lernmodule/view/".$this->module->getId()));
        }
    }

    public function move_action($module_id)
    {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", $this->course_id)) {
            throw new AccessDeniedException();
        }
        $this->module = Lernmodul::find($module_id);
        if (!$this->module->isWritable()) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/course/lernmodule/overview");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/lernmoduleplugin.js");
        PageLayout::setTitle(_("Lernmodul verschieben in andere Veranstaltung"));
        if ($this->module['type'] && !$this->module->isNew()) {
            $class = ucfirst($this->module['type'])."Lernmodul";
            $this->module = $class::buildExisting($this->module->toArray()); //toRawArray
        }
        if (Request::isPost() && Request::option("seminar_id") && $GLOBALS['perm']->have_studip_perm("tutor", Request::option("seminar_id"))) {
            PluginManager::getInstance()->setPluginActivated(
                $this->plugin->getPluginId(),
                Request::option("seminar_id"),
                true
            );
            $this->blocks = LernmodulBlock::findBySQL("seminar_id = ? ORDER BY position ASC", [Request::option("seminar_id")]);
            if (!count($this->blocks)) {
                $block = new LernmodulBlock();
                $block['seminar_id'] = Request::option("seminar_id");
                $block->store();
                $this->blocks[] = $block;
            }
            if (Request::submitted('move')) {
                $connection = $this->module->courseConnection($this->course_id);
                $connection['seminar_id'] = Request::option("seminar_id");

                //delete old dependencies:
                LernmodulDependency::deleteBySQL("seminar_id = ? AND module_id = ?", array(
                    $this->course_id,
                    $this->module->getId()
                ));

                $connection['block_id'] = $this->blocks[count($this->blocks) - 1]->getId();
                $connection->store();
            } elseif (Request::submitted('copy')) {
                $lernmodule = Lernmodul::createCopyFromModule($this->module);

                $connection = $lernmodule->courseConnection(Request::option("seminar_id"));
                $connection['block_id'] = $this->blocks[count($this->blocks) - 1]->getId();
                $connection->store();
            }



            PageLayout::postSuccess(_("Lernmodul wurde verschoben."));
            $this->redirect(PluginEngine::getURL($this->plugin, array('cid' => Request::option("seminar_id")), "lernmodule/overview"));
            return;
        }
        if (!$GLOBALS['perm']->have_perm("admin")) {
            $statement = DBManager::get()->prepare("
                SELECT seminare.*
                FROM seminare
                    INNER JOIN seminar_user ON (seminar_user.Seminar_id = seminare.Seminar_id)
                WHERE seminar_user.user_id = :user_id
                    AND seminar_user.status IN ('tutor', 'dozent')
                ORDER BY seminare.start_time + seminare.duration_time DESC, seminare.name ASC
            ");
            $statement->execute(array('user_id' => $GLOBALS['user']->id));
            $this->semcourses = [];
            $firstsemester_id = null;
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $coursedata) {
                $course = Course::buildExisting($coursedata);
                if ($course['duration_time'] >= 0) {
                    $semester = Semester::findByTimestamp($course['start_time'] + $course['duration_time']);
                    if ($firstsemester_id === null) {
                        $firstsemester_id = $semester->getId();
                    }
                    if (!isset($this->semcourses[$semester->getId()])) {
                        $this->semcourses[$semester->getId()] = [
                            'semester' => $semester,
                            'courses' => []
                        ];
                    }
                    $this->semcourses[$semester->getId()]['courses'][] = $course;
                } else {
                    if ($firstsemester_id === null) {
                        $semester = Semester::findCurrent();
                        $this->semcourses[$semester->getId()] = [
                            'semester' => $semester,
                            'courses' => []
                        ];
                        $firstsemester_id = $semester->getId();
                    }
                    $this->semcourses[$firstsemester_id]['courses'][] = $course;
                }
            }
            foreach ($this->semcourses as $index => $semcourse) {
                //now sort for name:
                $courses = $semcourse['courses'];
                usort($courses, function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });
                $this->semcourses[$index]['courses'] = $courses;
            }
        }
    }

}
