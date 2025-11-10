<?php

namespace lib\CoursewareBlocks;

/**
 * This class represents the content of a 'Pairing' task from H5P.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class PairingBlock extends LernmoduleBlock
{
    public static function getType(): string
    {
        return 'pairing';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Pairing');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, wo Paare von Inhalten (Bilder, Texte oder Audio) zu einander gematcht werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'Pairing',
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

