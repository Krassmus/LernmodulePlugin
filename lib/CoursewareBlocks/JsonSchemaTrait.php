<?php
namespace lib\CoursewareBlocks;
use Opis\JsonSchema\Schema;

// This method signature was changed in Stud.IP 6.0. To support both Stud.IP 5.x and 6.x,
// we must select a method implementation based on the Stud.IP version.
if (\StudipVersion::olderThan('6.0')) {
    trait JsonSchemaTrait
    {
        public static function getJsonSchema(): Schema
        {
            $schemaFile = __DIR__ . '/LernmoduleBlock.json';
            return Schema::fromJsonString(file_get_contents($schemaFile));
        }
    }
} else {
    trait JsonSchemaTrait
    {
        public static function getJsonSchema(): string
        {
            $schemaFile = __DIR__ . '/LernmoduleBlock.json';
            return file_get_contents($schemaFile);
        }
    }
}
