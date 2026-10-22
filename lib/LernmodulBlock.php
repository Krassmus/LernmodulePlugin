<?php

class LernmodulBlock extends SimpleORMap {

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_blocks';
        $config['has_many']['coursemodules'] = array(
            'class_name' => 'LernmodulCourse'
        );

        parent::configure($config);
    }

    /**
     * @return bool True iff the currently authenticated user has write permission for this Block
     */
    public function isWritable(): bool
    {
        $user = User::findCurrent();
        if (!$user) {
            return false;
        }
        return Seminar_Perm::get()->have_studip_perm('tutor', $this->seminar_id, $user->id);
    }
}
