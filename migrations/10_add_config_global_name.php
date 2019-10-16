<?php

class AddConfigGlobalName extends Migration
{
    function up()
    {
        Config::get()->create("LERNMODUL_GLOBAL_NAME", array(
            'type' => "string",
            'range' => "global",
            'section' => "LernmodulePlugin",
            'value' => "",
            'description' => "Der Standardname des Reiters in der Veranstaltung."
        ));
    }

    function down()
    {
        Config::get()->delete("LERNMODUL_GLOBAL_NAME");
    }
}