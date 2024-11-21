<?php

class AddPreviewConfigVariable extends Migration
{
    public function description()
    {
        return 'Add a config variable, LERNMODULE_PREVIEW, to toggle modules still in development.';
    }

    public function up()
    {
        Config::get()->create(
            'LERNMODULE_PREVIEW',
            [
                'type' => 'boolean',
                'value' => false,
                'section' => 'LernmodulePlugin',
                'description' => 'If set to true, Lernmodule that are still in development will be available for preview.'
            ]
        );
    }

    public function down()
    {
        Config::get()->delete('LERNMODULE_PREVIEW');
    }
}
