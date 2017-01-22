<?php

class LernmodulGameAttendance extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_game_attendances';
        $config['belongs_to']['game'] = array(
            'class_name' => 'LernmodulGame',
            'foreign_key' => 'game_id',
            'assoc_func' => 'find',
        );
        parent::configure($config);
    }

    public function delete()
    {
        $game = $this->game;
        $output = parent::delete();
        if (!count($game->attendances)) {
            $game->delete();
        }
    }
}