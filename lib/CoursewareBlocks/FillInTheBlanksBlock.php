<?php

namespace CoursewareLernmoduleBlocks;

use Courseware\BlockTypes\BlockType;
use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a 'Fill In The Blanks' type task.
 *
 * @author  Ann Yanich
 * @license GPL3 or any later version
 */
class FillInTheBlanksBlock extends BlockType
{
    public static function getType(): string
    {
        return 'fill-in-the-blanks';
    }

    public static function getTitle(): string
    {
        return dgettext('lernmoduleplugin', 'LMB - Fill in the Blanks');
    }

    public static function getDescription(): string
    {
        return dgettext(
            'lernmoduleplugin',
            'Eine Lernaufgabe, wo die Lücken in einem Lückentext ausgefüllt werden sollen.'
        );
    }

    public function initialPayload(): array
    {
        return [
            "initialized" => false,
            "task_type" => 'FillInTheBlanks',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__ . '/LernmoduleBlock.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public static function getCategories(): array
    {
        return ['interaction'];
    }

    public static function getContentTypes(): array
    {
        return ['text'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

    /**
     * Returns the decoded payload of the block associated with this instance.
     *
     * @return mixed the decoded payload
     */
    public function getPayload()
    {
        $decoded = $this->decodePayloadString($this->block['payload']);
        return $decoded;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if ('' != $payload['file_id']) {
            $payload['file_id'] = $this->copyFileById($payload['file_id'], $rangeId);
        }

        return $payload;
    }

    /**
     * get all files related to this block.
     *
     * @return \FileRef[] list of file references realted to this block
     */
    public function getFiles(): array
    {
        // TODO This method does not work for H5P vue tasks as currently stored.
        //  Possibly, the whole data
        //  schema for vue h5p tasks should be modified to store a list of file
        //  IDs somewhere.  At the moment, all uploaded files are dumped inside
        //  of the wysiwyg uploads folder, and references to them are saved ad-hoc
        //  in different places depending on the task type, e.g. for Flash Cards,
        //  you will find it in task_json->cards->[0...n]->image_url.
        $payload = $this->getPayload();
        if (!$payload['file_id'] && !$payload['folder_id']) {
            return [];
        }

        if ($payload['file_id']) {
            $files = [];

            if ($fileRef = \FileRef::find($payload['file_id'])) {
                $files[] = $fileRef;
            }

            return $files;
        } else {
            return \FileRef::findByFolder_id($payload['folder_id']);
        }
    }
}

