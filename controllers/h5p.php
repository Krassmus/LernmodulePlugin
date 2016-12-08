<?php

require_once __DIR__."/../vendor/h5p-php-library/h5p.classes.php";

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info")
                : Assets::image_path("icons/black/20/learnmodule")
        );
    }

    public function view_action($module_id)
    {
        $this->mod = new Lernmodul($module_id);
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/jquery.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-event-dispatcher.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-x-api.js");
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/h5p/h5p-x-api-events.js");
        PageLayout::addStylesheet($this->plugin->getPluginURL()."/assets/h5p.css");
        PageLayout::addHeadElement("script", array('src' => PluginEngine::getURL($this->plugin, array(), "h5p/javascript/".$module_id)));
    }

    public function javascript_action($module_id)
    {
        $this->set_layout(null);
        $this->mod = new Lernmodul($module_id);
    }
}