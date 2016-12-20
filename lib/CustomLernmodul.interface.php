<?php

interface CustomLernmodul
{
    /**
     * Returns true if in the given path is a learnmodule of this type
     * @param $path
     * @return boolean
     */
    static public function detect($path);

    /**
     * A callback to be executed right after installing a new package
     * @return void
     */
    public function afterInstall();

    /**
     * A Flexi_Template showing some input fields for the learning-module.
     * Input fields should have names like name="module[customdata][start_file]" to get stored
     * properly.
     * @return null|Flexi_Template
     */
    public function getEditTemplate();

    /**
     * Returns a Flexi_Template object to run the learning-module
     * @return Flexi_Template|null
     */
    public function getViewerTemplate($attempt);
}