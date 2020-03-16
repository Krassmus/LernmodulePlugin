<?php

class LernmodulDependency extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_dependencies';
        parent::configure($config);
    }
}
