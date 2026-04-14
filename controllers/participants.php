<?php

class ParticipantsController extends PluginController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    /**
     * @return void
     * @throws AccessDeniedException
     * Show a list of the participants in the current Context, including the number of
     * Lernmodule modules they have completed, where the user can click on each student's
     * name to view more details about their progress.
     */
    public function index_action()
    {
        $cid = Context::getId();
        if (!isset($cid) || !$this->canViewAllStudentsProgress($cid)) {
            throw new AccessDeniedException();
        }
        // TODO: Add translation for this string
        PageLayout::setTitle(_("Teilnehmende"));
        Navigation::activateItem("/course/lernmodule/participants");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
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
     * It should link to the student's 'Evaluation' page.
     */
    public function linkForStudent($student): string
    {
        return PluginEngine::getLink($this->plugin, [], "participants/evaluation/" . $student['user_id']);
    }

    public function evaluation_action($user_id)
    {
        $cid = Context::getId();
        if (!isset($cid) || !$this->canViewEvaluation($cid, $user_id)) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_("Auswertung"));
        Navigation::activateItem("/course/lernmodule/participants");
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
        $this->user_id = $user_id;
        $this->attempts = LernmodulAttempt::findByUserAndCourse($this->user_id, $cid);
    }

    /**
     * @param $context_id
     * @param $student_id
     * @return bool if the current user can view the evaluation for the given student.
     * Users can view their own evaluation. Dozents (or whatever minimum status is configured
     * in LERNMODUL_PARTICIPANT_EVALUATION) can view the evaluation of all students in the class
     */
    public function canViewEvaluation($context_id, $student_id): bool
    {
        return $this->canViewAllStudentsProgress($context_id) ||
            User::findCurrent()->id == $student_id;
    }

    /**
     * @param $context_id
     * @return bool True iff the current user has permission to look at the progress
     * each student has made in the Lernmodule in the given context ID.
     */
    public function canViewAllStudentsProgress($context_id): bool {
        $required_perm = Config::get()->LERNMODUL_PARTICIPANT_EVALUATION;
        return $required_perm && $GLOBALS['perm']->have_studip_perm($required_perm, $context_id);
    }

}
