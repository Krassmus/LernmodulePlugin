<?php

class AddH5pOptionSimpleviewsuccess extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmodule_h5plibs
            ADD COLUMN `simple_view_success` tinyint(1) DEFAULT 0 AFTER `runnable`
        ");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmodule_h5plibs
            DROP COLUMN `simple_view_success`
        ");
        SimpleORMap::expireTableScheme();
    }
}