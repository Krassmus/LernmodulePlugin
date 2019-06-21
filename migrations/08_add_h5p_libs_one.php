<?php

require_once __DIR__."/H5PMigration.php";

class AddH5pLibsMigrationOne extends H5PMigration
{
    function up()
    {
        $this->updateH5PLibs();
    }
}