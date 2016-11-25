<?php

require_once __DIR__."/lib/Lernmodul.php";

class LernmodulePlugin extends StudIPPlugin implements StandardPlugin {

    public function getTabNavigation($course_id)
    {
        $tab = new Navigation(_("Lernmodule"), PluginEngine::getURL($this, array(), "lernmodule/overview"));
        $tab->setImage(Icon::create("learnmodule", "info_alt"));
        return array('lernmodule' => $tab);
    }

    public function getIconNavigation($course_id, $last_visit, $user_id)
    {
        // TODO: Implement getIconNavigation() method.
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

}