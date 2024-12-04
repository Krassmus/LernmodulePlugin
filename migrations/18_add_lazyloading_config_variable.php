<?php

class AddLazyloadingConfigVariable extends Migration
{
    public function description()
    {
        return 'Add a config variable, LERNMODULE_LAZYLOADING, to toggle if media is loaded when in view or when the page is loaded.';
    }

    public function up()
    {
        Config::get()->create(
            'LERNMODULE_LAZYLOADING',
            [
                'type' => 'boolean',
                'value' => false,
                'section' => 'LernmodulePlugin',
                'description' => 'If set to true, media in Lernmodule will be loaded when in view and not when the page is loaded.'
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('LERNMODULE_LAZYLOADING');
    }
}
