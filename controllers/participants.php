<?php

use LernmodulePlugin\ProgressAuthority;

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
        if (!isset($cid) || !ProgressAuthority::canViewAllStudentsProgress($cid)) {
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
        if (!isset($cid) || !ProgressAuthority::canViewEvaluation($cid, $user_id)) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_("Auswertung"));
        if (ProgressAuthority::canViewAllStudentsProgress($cid)) {
            Navigation::activateItem("/course/lernmodule/participants");
        } else {
            Navigation::activateItem("/course/lernmodule/progress");
        }
        Navigation::getItem("/course/lernmodule")->setImage(
            Icon::create("learnmodule", "info")
        );
        $this->user_id = $user_id;
        $this->attempts = LernmodulAttempt::findByUserAndCourse($this->user_id, $cid);
    }

}
