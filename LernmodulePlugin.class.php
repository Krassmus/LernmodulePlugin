<?php

require_once __DIR__."/lib/CustomLernmodul.interface.php";
require_once __DIR__."/lib/Lernmodul.php";
require_once __DIR__."/lib/HtmlLernmodul.php";
require_once __DIR__."/lib/ScormLernmodul.php";
require_once __DIR__."/lib/H5pLernmodul.php";
require_once __DIR__."/lib/LernmodulAttempt.php";
require_once __DIR__."/lib/LernmodulCourse.php";
require_once __DIR__."/lib/LernmodulDependency.php";
require_once __DIR__."/lib/LernmodulGame.php";
require_once __DIR__."/lib/LernmodulGameAttendance.php";
require_once __DIR__."/lib/LernmodulAdmission/LernmodulAdmission.class.php";
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
    }

    public function getTabNavigation($course_id)
    {
        $tab = new Navigation(_("Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
        $tab->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info_alt")
                : Assets::image_path("icons/white/16/learnmodule")
        );
        $tab->addSubNavigation("overview", new Navigation(_("Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview")));
        $tab->addSubNavigation("participants", new Navigation(_("Teilnehmer"), PluginEngine::getURL($this, array(), "participants")));
        return array('lernmodule' => $tab);
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        $tab = new Navigation(_("Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
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
                version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                    ? Icon::create("learnmodule+new", "new", array('title' => sprintf(_("%s neue Lernmodule"), $new)))
                    : Assets::image_path("icons/red/20/new/learnmodule", array('title' => sprintf(_("%s neue Lernmodule"), $new)))
            );
        } else {
            $tab->setImage(
                version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                    ? Icon::create("learnmodule", "inactive", array('title' => _("Lernmodule")))
                    : Assets::image_path("icons/grey/20/learnmodule", array('title' => _("Lernmodule")))
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
    }

    static public function mayEditSandbox()
    {
        return $GLOBALS['perm']->have_perm("admin")
                    || RolePersistence::isAssignedRole($GLOBALS['user']->id, "Lernmodule-Admin");
    }

    public function getDisplayTitle()
    {
        return _("Lernmodule");
    }

    static public function bytesFromPHPIniValue($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }

    static public function extract_zip($file_name, $dir_name = '', $testonly = false) {
        $ret = false;
        if ($GLOBALS['ZIP_USE_INTERNAL']) {
            if (!class_exists("PclZip")) {
                if ($testonly) {
                    return Studip\ZipArchive::test($file_name);
                }

                return Studip\ZipArchive::extractToPath($file_name, $dir_name);
            } else {
                $archive = new PclZip($file_name);
                if ($testonly) {
                    $prop = $archive->properties();
                    $ret = (!is_array($prop));
                } else {
                    $ok = $archive->extract(PCLZIP_OPT_PATH, $dir_name, PCLZIP_CB_PRE_EXTRACT, 'pclzip_convert_filename_cb', PCLZIP_OPT_STOP_ON_ERROR);
                    $ret = (!is_array($ok));
                }
            }
        } else if (@file_exists($GLOBALS['UNZIP_PATH']) || ini_get('safe_mode')){
            if ($testonly){
                exec($GLOBALS['UNZIP_PATH'] . " -t -qq $file_name ", $output, $ret);
            } else {
                exec($GLOBALS['UNZIP_PATH'] . " -qq $file_name " . ($dir_name ? "-d $dir_name" : ""), $output, $ret);
            }
            $ret = $ret === 0;
        }
        return $ret;
    }

}