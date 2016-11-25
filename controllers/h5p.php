<?php

class H5pController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(Icon::create("learnmodule", "info"));
    }

    public function view_action($module_id)
    {
        $this->mod = new Lernmodul($module_id);
    }
}