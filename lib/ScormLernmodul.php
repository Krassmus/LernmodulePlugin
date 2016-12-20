<?php

class ScormLernmodul extends Lernmodul implements CustomLernmodul
{
    static public function detect($path)
    {
        return true;
    }

    public function afterInstall()
    {

    }

    public function getEditTemplate() {}

    public function getViewerTemplate($attempt)
    {

    }
}