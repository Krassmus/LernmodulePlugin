<?php

class BugfixSandbox extends Migration
{
    function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_module`
            MODIFY COLUMN `sandbox` tinyint(1) NOT NULL DEFAULT 1
        ");
        DBManager::get()->exec("
            UPDATE `lernmodule_module`
            SET `sandbox` = '1'
            WHERE `lernmodule_module`.`user_id` NOT IN (
                SELECT `roles_user`.`userid` AS `user_id`
                FROM `roles_user`
                    INNER JOIN `roles` ON (`roles_user`.`roleid` = `roles_user`.`roleid`)
                WHERE `roles`.`rolename` = 'Lernmodule-Admin'
                UNION DISTINCT SELECT `auth_user_md5`.`user_id`
                FROM `auth_user_md5`
                WHERE `perms` = 'root'
            )
        ");

        SimpleORMap::expireTableScheme();
    }

    function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `lernmodule_module`
            MODIFY COLUMN `sandbox` tinyint(4) NOT NULL DEFAULT 0
        ");
        SimpleORMap::expireTableScheme();
    }
}
