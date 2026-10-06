<?php

namespace lib\CoursewareBlocks;


/**
 * This class represents the content of a 'Drag The Words' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class DragTheWordsBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'drag-the-words';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Drag The Words');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der die richtigen Wörter in die passenden Lücken eines Textes gezogen werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'DragTheWords',
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

