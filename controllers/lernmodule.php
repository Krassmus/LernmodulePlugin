<?php

class LernmoduleController extends PluginController
{
    public function overview_action()
    {
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(Icon::create("learnmodule", "info"));
        $this->module = Lernmodul::findBySeminar_id($_SESSION['SessionSeminar']);
    }
}