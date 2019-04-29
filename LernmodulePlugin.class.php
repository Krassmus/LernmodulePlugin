<?php

require_once __DIR__."/lib/CustomLernmodul.interface.php";
require_once __DIR__."/lib/Lernmodul.php";
require_once __DIR__."/lib/HtmlLernmodul.php";
require_once __DIR__."/lib/ScormLernmodul.php";
require_once __DIR__."/lib/H5pLernmodul.php";
require_once __DIR__."/lib/LernmodulAttempt.php";
require_once __DIR__."/lib/LernmodulCourse.php";
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
            $nav = new Navigation(dgettext("lernmoduleplugin","H5P-Bibliotheken"), PluginEngine::getURL($this, array(), "h5p/admin_libraries"));
            Navigation::addItem("/admin/locations/h5p", $nav);
        }
    }

    public function getTabNavigation($course_id)
    {
        $this->settings = new LernmodulCourseSettings($course_id);
        $tab = new Navigation($this->settings['tabname'] ?: dgettext("lernmoduleplugin","Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
        $tab->setImage(
            Icon::create("learnmodule", "info_alt")
        );
        $tab->addSubNavigation("overview", new Navigation(dgettext("lernmoduleplugin","Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview")));
        $tab->addSubNavigation("participants", new Navigation(dgettext("lernmoduleplugin","Teilnehmer"), PluginEngine::getURL($this, array(), "participants")));
        return array('lernmodule' => $tab);
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $tab = new Navigation(dgettext("lernmoduleplugin","Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
        $new = Lernmodul::countBySQL("INNER JOIN lernmodule_courses USING (module_id) WHERE lernmodule_courses.seminar_id = :course_id AND chdate >= :last_visit AND user_id <> :user_id", array(
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
        parent::perform($unconsumed_path);
        bindtextdomain("lernmoduleplugin", DIR."/locale");
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
}
