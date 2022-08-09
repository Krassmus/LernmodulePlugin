<?php

class ParticipantsController extends PluginController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        Navigation::activateItem("/course/lernmodule/participants");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
    }

    public function index_action()
    {
        // TODO: Add translation for this string
        PageLayout::setTitle(_("Teilnehmende"));
        $this->module = Lernmodul::findBySQL(
            "INNER JOIN lernmodule_courses USING (module_id) WHERE lernmodule_courses.seminar_id = ? ORDER BY name ASC",
            array(Context::get()->id)
        );
        $this->students = Course::findCurrent()->members->filter(function ($student) {
            return $student['status'] === "autor";
        });
    }

    /**
     * Return the 'href' attribute for a given row of the Participants table.
     * It should link either to the student's 'Evaluation' page
     * or to the student's Stud.IP profile, depending on the plugin's configuration
     * and the user's status.
     */
    public function linkForStudent($student): string
    {
        if ($this->canViewEvaluation()) {
            return PluginEngine::getLink($this->plugin, [], "participants/evaluation/" . $student['user_id']);
        } else {
            return URLHelper::getLink("dispatch.php/profile", ['username' => $student['username']]);
        }
    }

    public function evaluation_action($user_id)
    {
        if (!$this->canViewEvaluation()) {
            throw new AccessDeniedException();
        }
        $this->user_id = $user_id;
        $this->attempts = LernmodulAttempt::findByUserAndCourse($this->user_id, Context::get()->id);
    }

    public function canViewEvaluation(): bool
    {
        return Config::get()->LERNMODUL_PARTICIPANT_EVALUATION
            && $GLOBALS['perm']->have_studip_perm(
                Config::get()->LERNMODUL_PARTICIPANT_EVALUATION,
                Context::get()->id
            );
    }

}
