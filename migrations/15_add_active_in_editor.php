<?php

class AddActiveInEditor extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_h5plibs`
            ADD COLUMN `allowed_in_editor` tinyint(1) NOT NULL DEFAULT 1 AFTER `allowed`
        ");
        DBManager::get()->exec("
            UPDATE `lernmodule_h5plibs`
            SET `allowed_in_editor` = `allowed`
        ");

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_module`
            DROP COLUMN `allowed_in_editor`
        ");
        SimpleORMap::expireTableScheme();
    }
}
