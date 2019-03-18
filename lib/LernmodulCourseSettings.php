<?php

class LernmodulCourseSettings extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_course_settings';
        parent::configure($config);
    }
}