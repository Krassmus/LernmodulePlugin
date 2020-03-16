<?php

class AddInfotextToModules extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses`
            ADD COLUMN `infotext` TEXT DEFAULT NULL
        ");
        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses`
            DROP COLUMN `infotext`
        ");
        SimpleORMap::expireTableScheme();
    }
}
