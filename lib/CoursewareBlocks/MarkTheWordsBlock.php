<?php

namespace lib\CoursewareBlocks;


/**
 * This class represents the content of a 'Mark The Words' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class MarkTheWordsBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'mark-the-words';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Mark The Words');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der die gesuchten Wörter im Text gefunden und markiert werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'MarkTheWords',
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

