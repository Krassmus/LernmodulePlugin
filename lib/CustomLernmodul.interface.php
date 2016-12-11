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

    public function getEditTemplate();
}