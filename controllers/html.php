<?php

class HtmlController extends PluginController
{

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
        $this->utf8decode_xhr = false;
    }

    public function set_configs_action() {
        if (!$GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            throw new AccessDeniedException();
        }
        $this->lernmodulcourse = new LernmodulCourse(array(Request::option("module_id"), Context::get()->id));
        if ($this->lernmodulcourse->isNew()) {
            throw new Exception("Kein gültiges Modul.");
        }
        if (Request::isPost()) {
            if ($this->lernmodulcourse['customdata'] !== null) {
                $this->lernmodulcourse['customdata']['configs'] = Request::getArray("configs");
            } else {
                $this->lernmodulcourse['customdata'] = array(
                    'configs' => Request::getArray("configs")
                );
            }
            $this->lernmodulcourse->store();
            PageLayout::postMessage(MessageBox::success(dgettext("lernmoduleplugin","Werte wurden gespeichert.")));
        }
    }

    public function get_url_action($module_id)
    {
        $this->module = Lernmodul::find($module_id);
        if (!$this->module->isWritable()) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(dgettext("lernmoduleplugin","Direktlink zum Lernmodul"));
    }

}
