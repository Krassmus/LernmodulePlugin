<?php

require_once __DIR__."/lib/CustomLernmodul.interface.php";
require_once __DIR__."/lib/Lernmodul.php";
require_once __DIR__."/lib/HtmlLernmodul.php";
require_once __DIR__."/lib/ScormLernmodul.php";
require_once __DIR__."/lib/H5pLernmodul.php";
require_once __DIR__."/lib/LernmodulAttempt.php";
require_once __DIR__."/lib/LernmodulCourse.php";
require_once __DIR__."/lib/LernmodulBlock.php";
require_once __DIR__."/lib/LernmodulCourseSettings.php";
require_once __DIR__."/lib/LernmodulDependency.php";
require_once __DIR__."/lib/LernmodulGame.php";
require_once __DIR__."/lib/LernmodulGameAttendance.php";
require_once __DIR__."/lib/LernmodulAdmission/LernmodulAdmission.class.php";
require_once __DIR__."/lib/H5P/H5PLib.php";
require_once 'app/controllers/plugin_controller.php';

if (!isset($GLOBALS['FILESYSTEM_UTF8'])) {
    $GLOBALS['FILESYSTEM_UTF8'] = true;
}

class LernmodulePlugin extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public function __construct()
    {
        parent::__construct();
        if (UpdateInformation::isCollecting()) {
            $data = Request::getArray("page_info");
            if (mb_stripos(Request::get("page"), "plugins.php/lernmoduleplugin") !== false && isset($data['Lernmodule'])) {
                $data['Lernmodule']['attempt_id'];
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
        if ($GLOBALS['perm']->have_perm("root")) {
            $nav = new Navigation(
                dgettext("lernmoduleplugin","H5P-Bibliotheken"),
                PluginEngine::getURL($this, array(), "h5p/admin_libraries")
            );
            Navigation::addItem("/admin/locations/h5p", $nav);
        }
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
                Icon::create("learnmodule", "inactive", array('title' => dgettext("lernmoduleplugin","Lernmodule")))
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
        $this->addStylesheet("assets/lernmodule.less");
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

    static public function bytesFromPHPIniValue($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
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
}
