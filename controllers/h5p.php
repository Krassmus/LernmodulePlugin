<?php

require_once __DIR__."/../vendor/h5p-php-library/h5p.classes.php";

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function editor_action($module_id = null)
    {
        if (!Context::get()->id || !$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
    }

    public function javascript_action($module_id)
    {
        $this->set_layout(null);
        $this->mod = new Lernmodul($module_id);
    }

    public function admin_libraries_action()
    {
        if (!$GLOBALS['perm']->have_perm("root")) {
            throw new AccessDeniedException();
        }
        Navigation::activateItem("/admin/locations/h5p");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5pstudip.js");
        PageLayout::setTitle(_("H5P-Bibliotheken"));
        $this->libs = H5PLib::findBySQL("1 = 1 ORDER BY name ASC, major_version DESC, minor_version DESC");
    }

    public function activate_library_action()
    {
        if (!$GLOBALS['perm']->have_perm("root") || !Request::isPost()) {
            throw new AccessDeniedException();
        }
        $lib = H5PLib::find(Request::option("lib_id"));
        $lib['allowed'] = Request::int("allowed", 0);
        $lib->store();
        $this->render_text("ok");
    }

    public function iframe_action($module_id)
    {
        $this->set_layout(null);
        $this->mod = new H5pLernmodul($module_id);
        if (!$this->mod->isAllowed()) {
            $libs = array();
            foreach (H5PLib::findMany($this->mod->findUnallowedLibraries()) as $lib) {
                $libs[] = $lib['name']." ".$lib['major_version'].".".$lib['minor_version'];
            }
            PageLayout::postInfo(_("Dieses H5P-Modul benutzt Bibliotheken, die noch nicht freigegeben sind."), $libs);
            $this->render_nothing();
            return;
        }

        $this->lang;
        $this->content = array(
            'id' => $module_id,
            'title' => $this->mod['name']
        );
        $this->styles = array(
            $this->plugin->getPluginURL().'/assets/h5p/h5p.css',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-confirmation-dialog.css',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-core-button.css'
        );
        $this->scripts = array(
            $this->plugin->getPluginURL().'/assets/h5p/jquery.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-event-dispatcher.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-x-api-event.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-x-api.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-content-type.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-confirmation-dialog.js',
            $this->plugin->getPluginURL().'/assets/h5p/h5p-action-bar.js'
        );
        $this->integration = $this->get_h5p_settings();

        foreach ($this->mod->getCSSFiles() as $css) {
            $this->styles[] = $this->mod->getH5pLibURL()."/".$css;
        }
        foreach ($this->mod->getJSFiles() as $js) {
            $this->scripts[] = $this->mod->getH5pLibURL()."/".$js;
        }
    }

    public function get_h5p_settings()
    {
        $libs = $this->mod->getLibs();
        $library = null;
        foreach ($libs as $lib) {
            if ($lib['runnable']) {
                $library = $lib;
                break;
            }
        }
        if (!$library) {
            throw new Exception("Module has no runnable library.");
        }
        //$h5p = $this->get_h5p_instance('interface');
        $settings = array(
            'baseUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'url' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId(),
            'postUserStatistics' => false,
            'ajax' => array(
                'setFinished' => URLHelper::getURL("plugins.php/lernmoduleplugin/h5p/set_finished"),
                'contentUserData' => ('admin-ajax.php?token=' . ('h5p_contentuserdata') . '&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId')
            ),
            'saveFreq' => 30,
            'siteUrl' => URLHelper::getURL("plugins.php/plugins.php/lernmoduleplugin/h5p/iframe/".$this->mod->getId()),
            'l10n' => array(
                'H5P' => "", //$core->getLocalization(),
            ),
            'hubIsEnabled' => false,
            'reportingIsEnabled' => FALSE,
            'libraryConfig' => null, //$h5p->getLibraryConfig(),
            'crossorigin' => null,
        );

        $settings['user'] = array(
            'name' => $GLOBALS['user']->getFullName(),
            'mail' => $GLOBALS['user']->email
        );
        $libraryname = $library['name']." ".$library['major_version'].".".$library['minor_version'];
        $settings['contents'] = array(
            "cid-" . $this->mod->getId() => array(
                'library' => $libraryname, //name of the runnable library with space instead of minus
                'jsonContent' => file_get_contents($this->mod->getPath()."/content/content.json"),
                'fullscreen' => 0,
                'exportUrl' => null,
                'embedCode' => '<iframe src="">',
                'resizeCode' => null,
                'url' => "",
                'contentUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId()."/content",
                'title' => $this->mod['name'],
                'displayOptions' => array(
                    'frame' => false,
                    'export' => false,
                    'embed' => false,
                    'copyright' => false,
                    'icon' => false
                ),
                'metadata' => array(
                    'title' => $this->mod['name'],
                    'license' => "U"
                ),
                'contentUserData' => array(
                    array('state' => "{}")
                )
            )
        );
        return $settings;
    }

    public function set_finished_action()
    {
        $module_id = Request::option("contentId");
        $score = Request::get("score");
        $max_score = Request::get("maxScore");
        $opened_time = Request::int("opened");
        $finished_time = Request::int("finished");
        $time = Request::get("time");

        $this->render_text("");
    }


}