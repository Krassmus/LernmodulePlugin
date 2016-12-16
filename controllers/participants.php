<?php

class ParticipantsController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule/participants");
        Navigation::getItem("/course/lernmodule")->setImage(
            version_compare($GLOBALS['SOFTWARE_VERSION'], "3.4", ">=")
                ? Icon::create("learnmodule", "info")
                : Assets::image_path("icons/black/20/learnmodule")
        );
    }

    public function index_action()
    {
        $this->module = Lernmodul::findBySQL("INNER JOIN lernmodule_courses USING (module_id) WHERE lernmodule_courses.seminar_id = ? ORDER BY name ASC", array($_SESSION['SessionSeminar']));
        $this->students = Course::findCurrent()->members->filter(function ($student) { return $student['status'] === "autor"; });
    }

    public function evaluation_action($username)
    {
        if (!Config::get()->LERNMODUL_PARTICIPANT_EVALUATION
            || !$GLOBALS['perm']->have_studip_perm(Config::get()->LERNMODUL_PARTICIPANT_EVALUATION, $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException();
        }
        $this->user_id = get_userid($username);
    }



}