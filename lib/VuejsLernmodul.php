<?php

class VuejsLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        throw new Exception('Not implemented');
    }

    public function afterInstall()
    {
        throw new Exception('Not implemented');
    }

    public function getEditTemplate()
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin", "Im Editor bearbeiten"),
            URLHelper::getURL("plugins.php/lernmoduleplugin/vuejseditor/edit/" . $this->getId()),
            Icon::create("edit", "clickable")
        );
        Sidebar::Get()->addWidget($actions);
    }

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin", "Ann's Javascript Test"),
            "#",
            Icon::create("play", "clickable"),
            array(
                'onClick' => "window.alert('hello :)  So kannst du JS vom Actions Widget aus nutzen.')"
            )
        );
        $actions->addLink(
            dgettext("lernmoduleplugin", "Im Editor bearbeiten"),
            URLHelper::getURL("plugins.php/lernmoduleplugin/vuejseditor/edit/" . $this->getId()),
            Icon::create("edit", "clickable")
        );
        Sidebar::Get()->addWidget($actions);

        $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
        $template = $templatefactory->open("vuejs/view.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("attempt", $attempt);
        $template->set_attribute(
            "coursemodule",
            LernmodulCourse::findOneBySQL(
                "module_id = ? AND seminar_id = ?",
                array($this->getId(), Context::get()->id)
            )
        );
        $template->set_attribute("game_attendance", $game_attendance);
        // TODO: Load configuration and make it available to both the editor and viewer
        $template->set_attribute("production", false);
        return $template;
    }

    public function getEvaluationTemplate($course_id)
    {
        $attempts = LernmodulAttempt::findbyCourseAndModule(Context::get()->id, $this->getId());
        $pointclasses = array();
        foreach ($attempts as $attempt) {
            if ($attempt['customdata']) {
                $data = $attempt['customdata']->getArrayCopy();
                foreach ((array)$data['points'] as $pointclass => $value) {
                    if (!in_array($pointclass, $pointclasses)) {
                        $pointclasses[] = $pointclass;
                    }
                }
            }
        }

        $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
        $template = $templatefactory->open("html/evaluation.php");
        $template->set_attribute("module", $this);
        $template->set_attribute("course_id", $course_id);
        $template->set_attribute("pointclasses", $pointclasses);
        $template->set_attribute("attempts", $attempts);
        return $template;
    }

    public function evaluateAttempt($attempt)
    {
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

}
