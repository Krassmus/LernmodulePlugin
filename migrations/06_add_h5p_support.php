<?php

class AddH5pSupport extends Migration
{

    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_h5plibs` (
                `lib_id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `major_version` int(11) DEFAULT NULL,
                `minor_version` int(11) DEFAULT NULL,
                `patch_version` int(11) DEFAULT NULL,
                `allowed` tinyint(1) NOT NULL DEFAULT '0',
                `runnable` tinyint(1) NOT NULL DEFAULT '0',
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`lib_id`),
                UNIQUE KEY `name` (`name`,`major_version`,`minor_version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_h5plib_module` (
                `module_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                `lib_id` int(11) NOT NULL,
                PRIMARY KEY (`module_id`,`lib_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmodule_attempts
            ADD COLUMN `processed` tinyint(1) DEFAULT '0' AFTER `successful`
        ");
        DBManager::get()->exec("
            DELETE a2.*
            FROM lernmodule_attempts AS a1
                JOIN lernmodule_attempts AS a2 ON (a1.attempt_id != a2.attempt_id AND a1.user_id = a2.user_id AND a1.module_id = a2.module_id)
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmodule_attempts
            ADD UNIQUE KEY `unique_userattempts` (`module_id`,`user_id`)
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmodule_h5plib_module`;
        ");
        DBManager::get()->exec("
            DROP TABLE `lernmodule_h5plibs`;
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmodule_attempts
            DROP COLUMN `processed`
        ");
        DBManager::get()->exec("
            ALTER TABLE lernmodule_attempts
            DROP KEY `unique_userattempts`
        ");
    }
}