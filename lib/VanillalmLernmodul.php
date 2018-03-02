<?php

class VanillalmLernmodul extends Lernmodul {

    public function init() {

        $table_schema = array(
            'concept' => "vanillalm",
            'version' => "1.0",
            'schema' => "url to json schema",
            'engine' => "sqlite",
            'tables' => array(
                'blocks' => array( //JSON table schema like http://frictionlessdata.io/guides/json-table-schema/
                    'fields' => array(
                        array(
                            'name' => "id",
                            'type' => "integer",
                            'description' => "primary key of table, id of the block",
                            'constraints' => array(
                                'required' => true,
                                'unique' => true
                            )
                        ),
                        array(
                            'name' => "position",
                            'type' => "integer",
                            'description' => "position of that block within the document",
                            'constraints' => array(
                                'required' => true
                            )
                        )
                    ),
                    'primaryKey' => "id"
                ),
                'layouts' => array( //JSON table schema like http://frictionlessdata.io/guides/json-table-schema/
                    'fields' => array(
                        array(
                            'name' => "id",
                            'type' => "integer",
                            'description' => "primary key of table, id of the layout",
                            'constraints' => array(
                                'required' => true,
                                'unique' => true
                            )
                        ),
                        array(
                            'name' => "name",
                            'type' => "text",
                            'description' => "name of the layout",
                            'constraints' => array(
                                'required' => true
                            )
                        )
                    ),
                    'primaryKey' => "id"
                )
            )
        );

        $sqlite_path = $this->getPath()."/vanillalm.sqlite";
        $this->createOrUpdateDatabase($sqlite_path, $table_schema);

        $sqlite = new PDO("sqlite:".$sqlite_path);

        $sqlite->exec("
            INSERT INTO blocks (
                position
            ) VALUES (1)
        ");

        die();

    }

    protected function createOrUpdateDatabase($path, $schema) {
        $sqlite = new PDO("sqlite:".$path);
        foreach ((array) $schema['tables'] as $table_name => $table) {
            //
            $statement = $sqlite->prepare("
                SELECT 1 
                FROM sqlite_master 
                WHERE type = 'table' 
                    AND name = ?
            ");
            $statement->execute(array($table_name));
            $exists = $statement->fetch(PDO::FETCH_COLUMN, 0);
            if ($exists) {
                $fields = $sqlite->query("PRAGMA table_info(\"".$table_name."\");")->fetchAll(PDO::FETCH_ASSOC);
                foreach ((array) $table['fields'] as $field) {
                    //check if field is correctly in the database
                    $exists = false;
                    foreach ($fields as $f) {
                        if ($f['name'] === $field['name']) {
                            $exists = true;
                            //check if column is correct

                        }
                    }
                    if (!$exists) {
                        $query = "ALTER TABLE \"".$table_name."\" ADD COLUMN \"".$field['name']."\" ".toupper($field['type'])." ";
                        if ($field['constraints']['required']) {
                            $query .= "NOT NULL ";
                        }
                        $sqlite->exec($query);
                    }

                }
            } else {
                $query = "CREATE TABLE IF NOT EXISTS \"".$table_name."\" (";
                foreach ((array) $table['fields'] as $index => $field) {
                    if ($field['name']) {
                        if ($index > 0) {
                            $query .= ", ";
                        }
                        $query .= " \"" . $field['name'] . "\" ".toupper($field['type'])." ";
                        if ($field['constraints']['required']) {
                            $query .= "NOT NULL ";
                        }
                        if ($table['primaryKey'] === $field['name']) {
                            $query .= "PRIMARY KEY ";
                        }
                    }
                }
                if (is_array($table['primaryKey'])) {
                    $query .= ", PRIMARY KEY (";
                    foreach ($table['primaryKey'] as $index => $row) {
                        if ($index > 0) {
                            $query .= ", ";
                        }
                        $query = "\”".$row."\"";
                    }
                    $query .= ") ";
                }
                $query .= ");";
                $success = $sqlite->exec($query);
                if ($success === false) {
                    var_dump($sqlite->errorInfo());
                }
            }
        }

    }


}