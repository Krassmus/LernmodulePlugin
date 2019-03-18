<?php

class AddCourseSettings extends Migration
{

    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_course_settings` (
                `seminar_id` varchar(32) NOT NULL DEFAULT '',
                `tabname` varchar(64) DEFAULT NULL,
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`seminar_id`)
            ) ENGINE=InnoDB
        
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmodule_course_settings`;
        ");
    }
}