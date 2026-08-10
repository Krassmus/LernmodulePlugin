<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Course Presentation' task from H5P.
 *
 * @author  Ann Yanich
 */
class CoursePresentationBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'crossword';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Course Presentation');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der eine Reihe von Folien angezeigt wird zum Durchblättern'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'CoursePresentation',
        ];
    }

    public static function getCategories(): array
    {
        return ['text', 'interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'image', 'multimedia'];
    }
}

