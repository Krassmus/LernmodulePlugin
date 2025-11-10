<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of an 'Interactive Video' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class InteractiveVideoBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'lmb-interactive-video';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Interactive Video');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Spielt ein mit Interaktionen angereichertes Video ab.  Andere LMB-Aufgaben können zu bestimmten Zeitpunkten im Video eingespielt werden.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'InteractiveVideo',
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

