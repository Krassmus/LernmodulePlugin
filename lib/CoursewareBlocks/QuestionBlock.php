<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Question' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class QuestionBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'question';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Frage');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der eine Frage durch die Auswahl einer oder mehrerer korrekter Antworten beantwortet werden soll.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'Question',
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
