<?php

class LernmodulVersuch extends SimpleORMap {

    static public function cleanUpDatabase()
    {
        self::deleteBySQL("chdate <= mkdate + 20 AND mkdate < UNIX_TIMESTAMP() - 60 * 2");
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_attempts';
        parent::configure($config);
    }
}