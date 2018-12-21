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


}