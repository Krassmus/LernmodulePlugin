<?php

class AllowEvaluationForStudents extends Migration {

    function up() {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses` 
            ADD COLUMN `evaluation_for_students` TINYINT(4) NOT NULL DEFAULT '0' AFTER `starttime`
        ");
        SimpleORMap::expireTableScheme();
    }

    function down() {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_courses` 
            DROP COLUMN `evaluation_for_students`
        ");
        SimpleORMap::expireTableScheme();
    }
}