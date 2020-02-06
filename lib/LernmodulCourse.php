<?php

class LernmodulCourse extends SimpleORMap
{

    static public function findbyblock_id($block_id)
    {
        return static::findBySQL("block_id = ? ORDER BY position ASC", [$block_id]);
    }

    static protected function configure($config = array())
    {
        $config['db_table'] = 'lernmodule_courses';
        $config['serialized_fields']['customdata'] = 'JSONArrayObject';
        $config['belongs_to']['module'] = array(
            'class_name' => 'Lernmodul',
            'foreign_key' => 'module_id',
            'assoc_func' => 'find',
        );
        parent::configure($config);
    }
}
