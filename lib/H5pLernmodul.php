<?php

class H5pLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        return true;
    }

    public function afterInstall()
    {
        //something with initializing new libraries?
    }

    public function getEditTemplate() {}

    public function getViewerTemplate($attempt, $game_attendance = null)
    {

    }

    public function getEvaluationTemplate($course_id) {
        return null;
    }

    public function evaluateAttempt($attempt) {
        return null;
    }
}