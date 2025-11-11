<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Find The Words' task from H5P.
 *
 * @author  Thomas Hellkamp
 * @license GPL3 or any later version
 */
class FindTheWordsBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'find-the-words';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Find The Words');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der Autoren eine Liste von Wörtern erstellen, die in einem Gitter dargestellt werden, und Lernende diese Wörter finden und auswählen sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'FindTheWords',
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

