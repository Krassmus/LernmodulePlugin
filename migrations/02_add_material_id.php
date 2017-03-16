<?php

class AddMaterialId extends Migration {

    function up() {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_module` 
            ADD COLUMN `material_id` varchar(32) NULL AFTER `user_id`
        ");

    }

    function down() {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_module` 
            DROP COLUMN `material_id`
        ");
    }
}