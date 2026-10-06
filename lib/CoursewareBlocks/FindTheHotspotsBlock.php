<?php

namespace lib\CoursewareBlocks;


/**
 * This class represents the content of a 'Find The Hotspots' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class FindTheHotspotsBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'find-the-hotspots';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Find The Hotspots');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, bei der durch Klicken auf ein Bild bestimmte Bereiche, sogenannte Hotspots, gefunden werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'FindTheHotspots',
        ];
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['image', 'multimedia'];
    }
}

