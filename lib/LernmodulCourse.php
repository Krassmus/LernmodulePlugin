<?php

class LernmodulCourse extends SimpleORMap
{

    static public function findbyblock_id($block_id)
    {
        return static::findBySQL("LEFT JOIN lernmodule_module USING (module_id) WHERE block_id = ? ORDER BY position ASC, lernmodule_module.name ASC", [$block_id]);
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
        $config['registered_callbacks']['after_delete'][] = function ($modulcourse) {
            $count = LernmodulCourse::countBySql("module_id = ? AND seminar_id != ?", [
                $modulcourse['module_id'],
                $modulcourse['seminar_id']
            ]);
            if (!$count) {
                Lernmodul::find($modulcourse['module_id'])->delete();
            }
        };
        parent::configure($config);
    }

    public function setDependencies($module_ids)
    {
        LernmodulDependency::deleteBySQL("seminar_id = ? AND module_id = ?", array(
            $this['seminar_id'],
            $this['module_id']
        ));
        foreach ($module_ids as $module_id) {
            $dependency = new LernmodulDependency();
            $dependency['seminar_id'] = $this['seminar_id'];
            $dependency['module_id'] = $this['module_id'];
            $dependency['depends_from_module_id'] = $module_id;
            $dependency->store();
        }
    }

    public function getDependencies()
    {
        return LernmodulDependency::findBySQL("module_id = ? AND seminar_id = ?", array(
            $this['module_id'],
            $this['seminar_id']
        ));
    }

    public function matchedPrerequisites()
    {
        foreach ($this->getDependencies() as $dependency) {
            $successes = LernmodulAttempt::countBySql("module_id = ? AND user_id = ? AND successful = '1'", array(
                $dependency['depends_from_module_id'],
                $GLOBALS['user']->id
            ));
            if (!$successes) {
                return false;
            }
        }
        return true;
    }
}
