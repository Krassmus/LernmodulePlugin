<?php

class AddDebugConfigVariable extends Migration
{
    public function description()
    {
        return 'Add a Config variable, LERNMODULE_DEBUG, to toggle the use of the Webpack dev server for our Javascript code';
    }

    public function up()
    {
        Config::get()->create(
            'LERNMODULE_DEBUG',
            [
                'type' => 'boolean',
                'value' => false,
                'section' => 'LernmodulePlugin',
                'description' => 'If set to true, the plugin\'s bundled Javascript files will be loaded from the dev server at ' .
                    'localhost:8080.  Otherwise, they will be loaded from the "vue/dist" folder.'
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('LERNMODULE_DEBUG');
    }
}
