<?php

namespace LernmodulePlugin;
use Config;
use User;

class ProgressAuthority {
    /**
     * @param $context_id
     * @param $student_id
     * @return bool if the current user can view the progress of the given student.
     * Users can view their own progress. Dozents (or whatever minimum status is configured
     * in LERNMODUL_PARTICIPANT_EVALUATION) can view the progress of all students in the class
     */
    public static function canViewProgress($context_id, $student_id): bool
    {
        return static::canViewAllStudentsProgress($context_id) ||
            User::findCurrent()->id == $student_id;
    }

    /**
     * @param $context_id
     * @return bool True iff the current user has permission to look at the progress
     * each student has made in the Lernmodule in the given context ID.
     */
    public static function canViewAllStudentsProgress($context_id): bool {
        $required_perm = Config::get()->LERNMODUL_PARTICIPANT_EVALUATION;
        return $required_perm && $GLOBALS['perm']->have_studip_perm($required_perm, $context_id);
    }

}
