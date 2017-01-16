<?php

class LernmodulGame extends SimpleORMap {

    static public function findOpenGames($course_id)
    {
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM (
                SELECT lernmodule_games.*, COUNT(*) AS players
                FROM lernmodule_games
                    INNER JOIN lernmodule_game_attendances ON (lernmodule_games.game_id = lernmodule_game_attendances.game_id)
                WHERE lernmodule_games.seminar_id = :course_id
                GROUP BY lernmodule_games.game_id
                ) as grouped_games
            WHERE players < max_players
        ");
        $statement->execute(array('course_id' => $course_id));
        $output = array();
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $data) {
            $output[] = self::buildExisting($data);
        }
        return $output;
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_games';
        $config['belongs_to']['module'] = array(
            'class_name' => 'Lernmodul',
            'foreign_key' => 'module_id',
            'assoc_func' => 'find',
        );
        $config['has_many']['attendances'] = array(
            'class_name' => 'LernmodulGameAttendance'
        );
        $config['serialized_fields']['parameter'] = 'JSONArrayObject';
        parent::configure($config);
    }

    public function isOpen()
    {
        return $this->max_players > count($this->attendances);
    }
}