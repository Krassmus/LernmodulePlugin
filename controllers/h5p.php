<?php

require_once __DIR__."/../vendor/h5p-php-library/h5p.classes.php";

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    /**
     * View some certain h5p-module in a course.
     * @param $module_id
     */
    public function view_action($module_id)
    {
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
        $this->mod = new Lernmodul($module_id);
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/jquery.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-event-dispatcher.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-x-api.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-x-api-events.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/h5p.css");
        PageLayout::addHeadElement("script", array('src' => PluginEngine::getURL($this->plugin, array(), "h5p/javascript/".$module_id)));
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
        //$h5p = $this->get_h5p_instance('interface');
        $settings = array(
            'baseUrl' => $GLOBALS['ABSOLUTE_URI_STUDIP'],
            'url' => URLHelper::getURL("plugins.php/plugins_packages/RasmusFuhse/LernmodulePluginData/moduledata/".$this->mod->getId()),
            'postUserStatistics' => false,
            'ajax' => array(
                'setFinished' => ('admin-ajax.php?token=' . ('h5p_result') . '&action=h5p_setFinished'),
                'contentUserData' => ('admin-ajax.php?token=' . ('h5p_contentuserdata') . '&action=h5p_contents_user_data&content_id=:contentId&data_type=:dataType&sub_content_id=:subContentId')
            ),
            'saveFreq' => 30,
            'siteUrl' => URLHelper::getURL("plugins.php/plugins.php/lernmoduleplugin/h5p/iframe/".$this->mod->getId()),
            'l10n' => array(
                'H5P' => "", //$core->getLocalization(),
            ),
            'hubIsEnabled' => false,
            'reportingIsEnabled' => FALSE,
            'libraryConfig' => "", //$h5p->getLibraryConfig(),
            'crossorigin' => null,
        );

        $settings['user'] = array(
            'name' => $GLOBALS['user']->getFullName(),
            'mail' => $GLOBALS['user']->email
        );
        $settings['contents'] = json_decode(file_get_contents($this->mod->getPath()."/content/content.json"), true);
        return $settings;
    }


}