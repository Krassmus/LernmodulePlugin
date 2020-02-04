<?php

class AddSinglecolumnOption extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_course_settings`
            ADD COLUMN `singlecolumn` tinyint(2) DEFAULT '0' AFTER `tabname`
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_course_settings`
            DROP COLUMN `singlecolumn`
        ");
    }
}
