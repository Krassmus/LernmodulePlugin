<?php

class CoursewareController extends PluginController
{
    public function editor_action()
    {
        $this->javascript_global_variables = [
            'LERNMODULE_DEBUG' => Config::get()->LERNMODULE_DEBUG,
            'LERNMODULE_PREVIEW' => Config::get()->LERNMODULE_PREVIEW,
            'LERNMODULE_LAZYLOADING' => Config::get()->LERNMODULE_LAZYLOADING,
        ];
    }
}
