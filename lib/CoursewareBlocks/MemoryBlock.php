<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Memory' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class MemoryBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'memory';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Memory');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Das klassische Memory Spiel.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'Memory',
        ];
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'image'];
    }
}

