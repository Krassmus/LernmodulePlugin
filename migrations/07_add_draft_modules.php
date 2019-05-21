<?php

class AddDraftModules extends Migration
{

    function up()
    {

        DBManager::get()->exec("
            ALTER TABLE lernmodule_module
            ADD COLUMN `draft` tinyint(1) DEFAULT '0' AFTER `user_id`
        ");

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE lernmodule_module
            DROP COLUMN `draft`
        ");
        SimpleORMap::expireTableScheme();
    }
}