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

    public function getViewerTemplate()
    {

    }
}