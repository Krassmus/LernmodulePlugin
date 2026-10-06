<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Sequencing' task from H5P.
 *
 * @author  Thomas Hellkamp
 * @license GPL3 or any later version
 */
class SequencingBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'sequencing';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Sequencing');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, in welcher Bilder in die richtige Reihenfolge gebracht werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'Sequencing',
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
