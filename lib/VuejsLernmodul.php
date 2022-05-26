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
        $templatefactory = new Flexi_TemplateFactory(__DIR__ . "/../views");
        $template = $templatefactory->open("vuejseditor/edit.php");
        $template->set_attribute("module", $this);
        return $template;
    }

    public function getViewerTemplate($attempt, $game_attendance = null)
    {
        $actions = new ActionsWidget();
        $actions->addLink(
            dgettext("lernmoduleplugin", "JS Test"),
            "#",
            Icon::create("play", "clickable"),
            array('onClick' => "window.alert('hello :)')")
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
