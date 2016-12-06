<?php

class LernmodulVersuch extends SimpleORMap {

    static public function cleanUpDatabase()
    {
        self::deleteBySQL("chdate <= mkdate + 20 AND mkdate < UNIX_TIMESTAMP() - 60 * 2");
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_attempts';
        $config['belongs_to']['modul'] = array(
            'class_name' => 'Lernmodul',
            'foreign_key' => 'modul_id',
            'assoc_func' => 'find',
        );
        parent::configure($config);
    }
}