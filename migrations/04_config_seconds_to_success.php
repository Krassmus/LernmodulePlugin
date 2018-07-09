<?php

class ConfigSecondsToSuccess extends Migration
{

    function up()
    {
        Config::get()->create("LERNMODUL_SECONDS_TO_SUCCESS", array(
            'type' => "integer",
            'range' => "global",
            'section' => "global",
            'value' => "30",
            'description' => "Nach wievielen Sekunden wird das anschauen eines HTML-Lernmoduls oder PDFs als Erfolg gewertet?"
        ));
    }

    function down()
    {
        Config::get()->delete("LERNMODUL_SECONDS_TO_SUCCESS");
    }
}