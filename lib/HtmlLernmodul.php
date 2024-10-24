<?php

class HtmlLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        return true;
    }

    public function afterInstall()
    {
        $data = [];
        if ($this['customdata']) {
            $data = $this['customdata']->getArrayCopy();
        }
        if (!$data || !$data['start_file'] || !file_exists($this->getPath()."/".$data['start_file'])) {
            if (file_exists($this->getPath()."/index.html")) {
                $data['start_file'] = "index.html";
            } else {
                $files = $this->scanForFiletypes(array("html", "htm"));
                if (count($files)) {
                    $data['start_file'] = $files[0];
                }
            }
        }
        if (!$data['start_file']) {
            $files = $this->scanForFiletypes(array("pdf"));
            $data['start_file'] = $files[0];
        }
        $this['customdata'] = $data;
        $this->store();
    }

    public function getEditTemplate()
    {
        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("html/edit.php");
        $template->set_attribute("module", $this);
        return $template;
    }

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin","Vollbild"),
            "#",
            Icon::create("play", "clickable"),
            array('onClick' => "STUDIP.Lernmodule.requestFullscreen(); return false;")
        );
        Sidebar::Get()->addWidget($actions);

        $myorigin = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
        $myorigin .= '://'.$_SERVER['SERVER_NAME'];

        if ($GLOBALS['perm']->have_studip_perm("tutor", Context::get()->id)) {
            $actions = Sidebar::Get()->getWidget("actions");
            $actions->addLink(
                dgettext("lernmoduleplugin","Konfigurieren"),
                URLHelper::getURL("plugins.php/lernmoduleplugin/html/set_configs"),
                Icon::create("admin", "clickable"),
                array(
                    'data-dialog' => 1,
                    'class' => "configure",
                    'onClick' => "STUDIP.Lernmodule.editConfigs(); return false;"
                )
            );
            $actions->addLink(
                dgettext("lernmoduleplugin","URL kopieren"),
                URLHelper::getURL("plugins.php/lernmoduleplugin/html/get_url/".$this->getId()),
                Icon::create("code", "clickable"),
                array(
                    'data-dialog' => "size=auto"
                )
            );
        }

        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("html/view.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("attempt", $attempt);
        $template->set_attribute("coursemodule", LernmodulCourse::findOneBySQL("module_id = ? AND seminar_id = ?", array($this->getId(), Context::get()->id)));
        $template->set_attribute("game_attendance", $game_attendance);
        $template->set_attribute("myorigin", $myorigin);
        return $template;
    }

    public function getEvaluationTemplate($course_id) {
        $attempts = LernmodulAttempt::findbyCourseAndModule(Context::get()->id, $this->getId());
        $pointclasses = array();
        foreach ($attempts as $attempt) {
            if ($attempt['customdata']) {
                $data = $attempt['customdata']->getArrayCopy();
                if (!empty($data['points'])) {
                    foreach ((array)$data['points'] as $pointclass => $value) {
                        if (!in_array($pointclass, $pointclasses)) {
                            $pointclasses[] = $pointclass;
                        }
                    }
                }
            }
        }


        $templatefactory = new Flexi_TemplateFactory(__DIR__."/../views");
        $template = $templatefactory->open("html/evaluation.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("course_id", $course_id);
        $template->set_attribute("pointclasses", $pointclasses);
        $template->set_attribute("attempts", $attempts);
        return $template;
    }

    public function evaluateAttempt($attempt) {
        $output = array();
        if ($attempt['customdata']['points']) {
            $output = $attempt['customdata']['points']->getArrayCopy();
        }
        if ($attempt['customdata']['attributes']) {
            foreach ($attempt['customdata']['attributes'] as $name => $value) {
                if (!isset($output[$name])) {
                    $output[$name] = $value;
                }
            }
        }
        return $output;
    }

    public function getStartURL($secret = null, $urlencode = false)
    {
        if ($this['url']) {
            return URLHelper::getURL($this['url'], array('vanillalm_secret' => $secret), true);
        } else {
            $url = $this['customdata']['start_file'] ?? "index.html";
            if ($urlencode) {
                $url = implode('/', array_map('rawurlencode', explode('/', $url)));
            }
            return $this->getDataURL() . "/" . $url . ($secret ? "?vanillalm_secret=" . urlencode($secret) : "");
        }
    }
}
