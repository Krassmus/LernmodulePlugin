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
    }
}