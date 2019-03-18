<?php

class ScormController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
    }

    public function view_action($module_id)
    {
        PageLayout::addScript($this->plugin->getPluginURL()."/assets/scorm.js");
        $this->mod = new Lernmodul($module_id);
        $this->xml = new DOMDocument();
        $this->xml->load($this->mod->getPath()."/imsmanifest.xml");
        $this->attempt = new LernmodulAttempt();
        $this->attempt->setData(array(
            'user_id' => $GLOBALS['user']->id,
            'module_id' => $module_id
        ));
        $this->attempt->store();
    }

}