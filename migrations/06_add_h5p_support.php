<?php

class AddH5pSupport extends Migration
{

    function up()
    {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_h5plibs` (
                `lib_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
                `name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `major_version` int(11) DEFAULT NULL,
                `minor_version` int(11) DEFAULT NULL,
                `allowed` tinyint(1) NOT NULL DEFAULT '0',
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`lib_id`),
                UNIQUE KEY `name` (`name`,`major_version`,`minor_version`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_h5plib_module` (
                `module_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                `lib_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
                PRIMARY KEY (`module_id`,`lib_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    function down()
    {
        DBManager::get()->exec("
            DROP TABLE `lernmodule_h5plibs`;
        ");
    }
}