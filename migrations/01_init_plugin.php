<?php

class InitPlugin extends Migration {

    function up() {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_module` (
                `module_id` varchar(32) NOT NULL DEFAULT '',
                `user_id` varchar(32) NOT NULL DEFAULT '',
                `seminar_id` varchar(32) NOT NULL DEFAULT '',
                `name` varchar(64) NOT NULL DEFAULT '',
                `type` varchar(16) NOT NULL DEFAULT 'html',
                `start_file` varchar(64) DEFAULT NULL,
                `image` varchar(128) DEFAULT NULL,
                `sandbox` tinyint(4) NOT NULL DEFAULT '0',
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`module_id`),
                KEY `user_id` (`user_id`),
                KEY `seminar_id` (`seminar_id`)
            ) ENGINE=InnoDB
        ");
    }

    function down() {
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_module`;
        ");
    }
}