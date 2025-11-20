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
        // This action is displayed inside of an iframe, so we have to import the courseware CSS ourselves.
        // This is admittedly unideal.
        // See #44 https://gitlab.uni-oldenburg.de/it-dienste/stud.ip/plugins/lernmodule/-/issues/44
        // (Prior to 6.0, the courseware css was part of studip-base.css, which is always present.)
        if (\StudipVersion::newerThan('6.0')) {
            PageLayout::addStylesheet("courseware.css");
        }
    }
}
