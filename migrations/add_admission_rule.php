<?php

class AddAdmissionRule extends Migration {

    function up() {
        DBManager::get()->exec("
            INSERT INTO `admissionrules`
            SET `ruletype` = 'LernmodulAdmission',
                `active` = 1,
                `path` = 'public/plugins_packages/RasmusFuhse/LernmodulePlugin/lib/LernmodulAdmission',
                `mkdate` = UNIX_TIMESTAMP()
        ");
        DBManager::get()->exec("
            CREATE TABLE `lernmodule_admissionrules` (
                `rule_id` varchar(32) NOT NULL DEFAULT '',
                `module_id` varchar(32) NOT NULL DEFAULT '',
                `seminar_id` varchar(32) DEFAULT NULL,
                PRIMARY KEY (`rule_id`)
            ) ENGINE=InnoDB
        ");
    }

    function down() {
        DBManager::get()->exec("
            DELETE FROM `admissionrules`
            WHERE `ruletype` = 'LernmodulAdmission'
        ");
        DBManager::get()->exec("
            DELETE FROM `courseset_rule`
            WHERE `type` = 'LernmodulAdmission'
        ");
        DBManager::get()->exec("
            DROP TABLE IF EXISTS `lernmodule_admissionrules`;
        ");
    }
}