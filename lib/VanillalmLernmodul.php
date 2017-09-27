<?php

class VanillalmLernmodul extends Lernmodul {

    public function init() {
        $sqlite_path = $this->getPath()."/vanillalm.sqlite";
        $sqlite = new PDO("sqlite:".$sqlite_path);

        //create table for blocks and one block
        $success = $sqlite->exec("
            CREATE TABLE IF NOT EXISTS blocks (
                id INTEGER PRIMARY KEY,
                position INTEGER NOT NULL
            )
        ");
        if ($success === false) {
            var_dump($sqlite->errorInfo());
        }
        var_dump($success);

        $sqlite->exec("
            INSERT INTO blocks (
                position
            ) VALUES (1)
        ");

        $success = $sqlite->exec("
            CREATE TABLE IF NOT EXISTS layouts (
                id INTEGER PRIMARY KEY,
                name TEXT
            )
        ");
        if ($success === false) {
            var_dump($sqlite->errorInfo());
        }

        die();

    }


}