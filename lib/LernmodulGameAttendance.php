<?php

class LernmodulGameAttendance extends SimpleORMap
{
    static protected function configure($config = [])
    {
        $config['db_table']           = 'lernmodule_game_attendances';
        $config['belongs_to']['game'] = [
            'class_name'  => 'LernmodulGame',
            'foreign_key' => 'game_id',
            'assoc_func'  => 'find',
        ];

        $config['registered_callbacks']['before_delete'][] = function () {
            if (!count($this->game->attendances)) {
                $this->game->delete();
            }
        };
        parent::configure($config);
    }
}
