<?php

if (!isset($GLOBALS['FILESYSTEM_UTF8'])) {
    $GLOBALS['FILESYSTEM_UTF8'] = true;
}

class LernmodulePlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin
{
    public function __construct()
    {
        parent::__construct();
        StudipAutoloader::addAutoloadPath(__DIR__ . '/lib');
        StudipAutoloader::addAutoloadPath(__DIR__ . '/lib/H5P');
        if (UpdateInformation::isCollecting()) {
            $data = Request::getArray("page_info");
            if (mb_stripos(Request::get("page"), "plugins.php/lernmoduleplugin") !== false && isset($data['Lernmodule'])) {
                $attempt = new LernmodulAttempt($data['Lernmodule']['attempt_id']);
                if ($attempt['user_id'] === $GLOBALS['user']->id) {
                    if ($data['Lernmodule']['customData']) {
                        $attempt['customData'] = $data['Lernmodule']['customData'];
                    }
                    if (!$attempt['successful']) {
                        $attempt['chdate'] = time();
                    }
                    $attempt->store();
                }
            }
        }
        if ($GLOBALS['perm']->have_perm("root") && Navigation::hasItem('/admin/locations')) {
            $nav = new Navigation(
                dgettext("lernmoduleplugin","H5P-Bibliotheken"),
                PluginEngine::getURL($this, array(), "h5p/admin_libraries")
            );
            Navigation::addItem("/admin/locations/h5p", $nav);
        }
        NotificationCenter::addObserver($this, "removeLernmoduleFromDeletedCourse", "CourseDidDelete");
    }

    public function getTabNavigation($course_id)
    {
        $this->settings = new LernmodulCourseSettings($course_id);
        $tabname = $this->settings['tabname'] ?: (Config::get()->LERNMODUL_GLOBAL_NAME ?: dgettext("lernmoduleplugin","Lernmodule"));
        $tab = new Navigation(
            $tabname,
            PluginEngine::getURL($this, array(), "lernmodule/overview")
        );
        $tab->setImage(
            Icon::create("learnmodule", "info_alt")
        );
        $tab->addSubNavigation("overview", new Navigation($tabname, PluginEngine::getURL($this, array(), "lernmodule/overview")));
        $tab->addSubNavigation("participants", new Navigation(dgettext("lernmoduleplugin","Teilnehmer"), PluginEngine::getURL($this, array(), "participants")));
        return array('lernmodule' => $tab);
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $tab = new Navigation(dgettext("lernmoduleplugin","Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
        $new = Lernmodul::countBySQL("INNER JOIN lernmodule_courses USING (module_id) WHERE lernmodule_courses.seminar_id = :course_id AND lernmodule_module.chdate >= :last_visit AND user_id <> :user_id", array(
            'course_id' => $course_id,
            'last_visit' => $last_visit,
            'user_id' => $GLOBALS['user']->id
        ));
        if (!$new) {
            $new = count(LernmodulGame::findOpenGames($course_id));
        }
        if ($new > 0) {
            $tab->setImage(
                Icon::create("learnmodule+new", "new", array('title' => sprintf(dgettext("lernmoduleplugin","%s neue Lernmodule"), $new)))
            );
        } else {
            $tab->setImage(
                Icon::create("learnmodule", "clickable", array('title' => dgettext("lernmoduleplugin","Lernmodule")))
            );
        }
        return $tab;
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return null;
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }

    public function perform($unconsumed_path)
    {
        $this->addStylesheet("assets/lernmodule.scss");
        bindtextdomain("lernmoduleplugin", $this->getPluginPath()."/locale");
        bind_textdomain_codeset("lernmoduleplugin", 'UTF-8');
        parent::perform($unconsumed_path);
    }

    static public function mayEditSandbox()
    {
        return $GLOBALS['perm']->have_perm("admin")
                    || RolePersistence::isAssignedRole($GLOBALS['user']->id, "Lernmodule-Admin");
    }

    public function getDisplayTitle()
    {
        return dgettext("lernmoduleplugin","Lernmodule");
    }

    public function removeLernmoduleFromDeletedCourse($event, $course)
    {
        LernmodulCourse::deleteBySQL("seminar_id = ?", [$course->getId()]);
        LernmodulCourseSettings::deleteBySQL("seminar_id = ?", [$course->getId()]);
    }

    static public function bytesFromPHPIniValue($val) {
        $lastChar = strtolower(substr($val, -1));
        $val = (int)trim($val);
        switch($lastChar) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }


    /**
     * Processes a topological sort. We need this for H5P support.
     * @param $nodeids : array of ids
     * @param array $edges : array of arrays like array(node1_id, node2_id)
     * @return array|bool : either the sorted array of ids or false if the graph has cycles.
     */
    static public function topologicalSort($nodeids, $edges) {
        $L = $S = $nodes = array();
        foreach($nodeids as $id) {
            $nodes[$id] = array(
                'in'=>array(),
                'out'=>array()
            );
            foreach($edges as $e) {
                if ($id == $e[0]) {
                    $nodes[$id]['out'][] = $e[1];
                }
                if ($id == $e[1]) {
                    $nodes[$id]['in'][] = $e[0];
                }
            }
        }
        foreach ($nodes as $id => $n) {
            if (empty($n['in'])) {
                $S[] = $id;
            }
        }
        while (!empty($S)) {
            $L[] = $id = (string) array_shift($S);
            foreach($nodes[$id]['out'] as $m) {
                $nodes[$m]['in'] = array_diff($nodes[$m]['in'], array($id));
                if (empty($nodes[$m]['in'])) {
                    $S[] = $m;
                }
            }
            $nodes[$id]['out'] = array();
        }
        foreach($nodes as $n) {
            if (!empty($n['in']) or !empty($n['out'])) {
                return false; // not sortable as graph is cyclic
            }
        }
        return $L;
    }

    public function getMetadata() {
        $metadata = parent::getMetadata();
        $metadata['pluginname'] = dgettext("lernmoduleplugin", "Lernmodule");
        $metadata['displayname'] = dgettext("lernmoduleplugin", "Lernmodule");
        $metadata['descriptionlong'] = dgettext("lernmoduleplugin", "Ein Ort, um Lernmodule zu erstellen, hochzuladen oder zu verlinken. Es können Abhängigkeiten zwischen Lernmodulen definiert werden, sodass Lernmodul B erst ausgeführt werden kann, wenn Lernmodul A absolviert worden ist. Auch zeitgesteuerte Lernmodule sind möglich, die erst ab einem bestimmten Datum sichtbar sind. Man kann die Lernmodule in Darstellungsblöcke gruppieren, die je noch einen einleitenden Text bekommen. Die Reihenfolge wird per Drag&Drop bestimmt.");
        $metadata['summary'] = dgettext("lernmoduleplugin", "Lernmodule in Stud.IP");
        $metadata['keywords'] = dgettext("lernmoduleplugin", "H5P-Lernmodule zum Hochladen oder selbst erstellen durch eigenen Editor; HTML-Lernmodule zum Hochladen oder Verlinken (als URL)");
        return $metadata;
    }
}
