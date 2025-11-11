<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Fill In The Blanks' type task.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class FillInTheBlanksBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'fill-in-the-blanks';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Fill In The Blanks');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der die Lücken in einem Text ausgefüllt werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'FillInTheBlanks',
        ];
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }
}

