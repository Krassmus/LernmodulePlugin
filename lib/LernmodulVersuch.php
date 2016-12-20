<?php

class LernmodulVersuch extends SimpleORMap {

    static public function cleanUpDatabase()
    {
        //self::deleteBySQL("successful IS NULL AND chdate <= mkdate + 20 AND mkdate < UNIX_TIMESTAMP() - 60 * 2");
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