<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Crossword' task from H5P.
 *
 * @author  Ann Yanich
 */
class CrosswordBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'crossword';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Crossword');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der Autoren eine Liste von Wörtern erstellen, die in einem Kreuzworträtsel dargestellt werden, und Lernende dieses Kreuzworträtsel lösen sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'Crossword',
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

