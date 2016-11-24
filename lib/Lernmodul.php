<?php

class Lernmodul extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_module';
        parent::configure($config);
    }
}