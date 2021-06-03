<?php

require_once __DIR__."/H5PMigration.php";

class InitPlugin extends H5PMigration {

    function up() {
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_module` (
                `module_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NOT NULL,
                `name` varchar(64) NOT NULL,
                `type` varchar(16) NOT NULL DEFAULT 'html',
                `url` varchar(256) NULL,
                `customdata` TEXT NULL,
                `image` varchar(256) DEFAULT NULL,
                `sandbox` tinyint(4) NOT NULL DEFAULT '0',
                `chdate` int(11) DEFAULT NULL,
                `mkdate` int(11) DEFAULT NULL,
                PRIMARY KEY (`module_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            INSERT IGNORE INTO `roles` (`rolename`, `system`)
            VALUES
                ('Lernmodule-Admin', 'n');
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_attempts` (
                `attempt_id` varchar(32) NOT NULL,
                `module_id` varchar(32) NOT NULL,
                `user_id` varchar(32) NULL,
                `successful` tinyint(4) NULL,
                `customdata` TEXT NULL,
                `mkdate` int(11) NOT NULL,
                `chdate` int(11) NOT NULL,
                PRIMARY KEY (`attempt_id`),
                KEY `module_id` (`module_id`),
                KEY `user_id` (`user_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_dependencies` (
                `dependency_id` varchar(32) NOT NULL,
                `seminar_id` varchar(32) NOT NULL,
                `module_id` varchar(32) NOT NULL,
                `depends_from_module_id` varchar(32) NOT NULL,
                PRIMARY KEY (`dependency_id`),
                KEY `seminar_id` (`seminar_id`),
                KEY `module_id` (`module_id`),
                KEY `depends_from_module_id` (`depends_from_module_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_courses` (
                `module_id` varchar(32) NOT NULL,
                `seminar_id` varchar(32) NOT NULL,
                `anonymous_attempts` tinyint(4) NOT NULL DEFAULT '0',
                `customdata` TEXT NULL,
                `starttime` bigint(20) DEFAULT NULL,
                PRIMARY KEY (`module_id`, `seminar_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_games` (
                `game_id` varchar(32) NOT NULL DEFAULT '',
                `seminar_id` varchar(32) NOT NULL DEFAULT '',
                `user_id` varchar(32) DEFAULT NULL,
                `module_id` varchar(32) NOT NULL,
                `parameter` text,
                `max_players` int(11) DEFAULT NULL,
                `closed` tinyint(11) NOT NULL DEFAULT '0',
                `chdate` bigint(20) DEFAULT NULL,
                `mkdate` bigint(20) DEFAULT NULL,
                PRIMARY KEY (`game_id`)
            ) ENGINE=InnoDB
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_game_attendances` (
                `attendance_id` varchar(32) NOT NULL DEFAULT '',
                `game_id` varchar(32) DEFAULT NULL,
                `user_id` varchar(32) DEFAULT NULL,
                `chdate` bigint(20) DEFAULT NULL,
                `mkdate` bigint(20) DEFAULT NULL,
                PRIMARY KEY (`attendance_id`)
            ) ENGINE=InnoDB
        ");
        Config::get()->create("LERNMODUL_PARTICIPANT_EVALUATION", array(
            'type' => "string",
            'value' => "dozent",
            'range' => "global",
            'section' => "LernmodulePlugin",
            'description' => "Ab welchem Status darf jemand die Nutzerauswertung der Lernmodule sehen (autor, tutor, dozent, admin, root, leer lassen)."
        ));
        Config::get()->create("LERNMODUL_DATA_PATH", array(
            'type' => "string",
            'value' => "",
            'range' => "global",
            'section' => "LernmodulePlugin",
            'description' => "Der absolute Pfad auf der Festplatte, wo die Lernmodule gespeichert werden sollen. Ist der Wert leer, sind sie in einem parallelen Pluginordner. Nur zusammen mit LERNMODUL_DATA_URL angeben."
        ));
        Config::get()->create("LERNMODUL_DATA_URL", array(
            'type' => "string",
            'value' => "",
            'range' => "global",
            'section' => "LernmodulePlugin",
            'description' => "Die URL, unter der die Lernmodule stecken. Es kann sinnvoll sein, diese URL unter einer Subdomain zu haben, damit Lernmodule einen anderen Origin haben."
        ));
        //StudipCacheFactory::getCache()->expire(RolePersistence::ROLES_CACHE_KEY);
    }

    function down() {
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_module`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_attempts`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_dependencies`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_courses`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_games`;
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_game_attendances`;
        ");
        DBManager::get()->exec("
            DELETE FROM `roles` WHERE `rolename` = 'Lernmodule-Admin';
        ");
        $path = Config::get()->LERNMODUL_DATA_PATH;
        if (!$path) {
            $path = __DIR__."/../../LernmodulePluginData";
        }
        Config::get()->delete("LERNMODUL_PARTICIPANT_EVALUATION");
        Config::get()->delete("LERNMODUL_DATA_PATH");
        Config::get()->delete("LERNMODUL_DATA_URL");
        StudipCacheFactory::getCache()->expire(RolePersistence::ROLES_CACHE_KEY);

        if (file_exists($path)) {
            $this->rrmdir($path);
        }
    }
}
