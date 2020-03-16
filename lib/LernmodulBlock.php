<?php

class LernmodulBlock extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_blocks';
        $config['has_many']['coursemodules'] = array(
            'class_name' => 'LernmodulCourse'
        );

        parent::configure($config);
    }
}
