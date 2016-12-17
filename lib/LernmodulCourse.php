<?php

class LernmodulCourse extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_courses';
        $config['serialized_fields']['customdata'] = 'JSONArrayObject';
        parent::configure($config);
    }
}