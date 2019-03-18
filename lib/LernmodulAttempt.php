<?php

class LernmodulAttempt extends SimpleORMap {

    static public function getByModule($module_id, $user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        $attempt = self::findOneBySQL("user_id = :user_id AND module_id = :module_id", array(
            'user_id' => $user_id,
            'module_id' => $module_id
        ));
        if (!$attempt) {
            $attempt = new LernmodulAttempt();
            $attempt['user_id'] = $user_id;
            $attempt['module_id'] = $module_id;
        }
        $attempt->store();
        return $attempt;
    }

    static public function findbyCourseAndModule($seminar_id, $module_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT lernmodule_attempts.*
            FROM lernmodule_attempts
                INNER JOIN seminar_user ON (seminar_user.user_id = lernmodule_attempts.user_id)
            WHERE seminar_user.Seminar_id = :seminar_id
                AND lernmodule_attempts.module_id = :module_id
            ORDER BY lernmodule_attempts.mkdate ASC
        ");
        $statement->execute(array(
            'module_id' => $module_id,
            'seminar_id' => $seminar_id
        ));
        $attempts = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $attempts[] = self::buildExisting($data);
        }
        return $attempts;
    }

    static public function findByUserAndCourse($user_id, $seminar_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT lernmodule_attempts.*
            FROM lernmodule_attempts
                INNER JOIN lernmodule_courses ON (lernmodule_attempts.module_id = lernmodule_courses.module_id)
            WHERE lernmodule_attempts.user_id = :user_id
                AND lernmodule_courses.seminar_id = :seminar_id
            ORDER BY lernmodule_attempts.mkdate ASC
        ");
        $statement->execute(array(
            'user_id' => $user_id,
            'seminar_id' => $seminar_id
        ));
        $attempts = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $attempts[] = self::buildExisting($data);
        }
        return $attempts;
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_attempts';
        $config['belongs_to']['modul'] = array(
            'class_name' => 'Lernmodul',
            'foreign_key' => 'module_id',
            'assoc_func' => 'find',
        );
        $config['serialized_fields']['customdata'] = 'JSONArrayObject';
        parent::configure($config);
    }
}