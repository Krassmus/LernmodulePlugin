<?php
/**
 * Base class for all Lernmodule Courseware Blocks.
 * TODO #42 Implement import/export of files -- see https://gitlab.uni-oldenburg.de/it-dienste/stud.ip/plugins/lernmodule/-/issues/42
 */

namespace lib\CoursewareBlocks;
use Courseware\BlockTypes\BlockType;

abstract class LernmoduleBlock extends BlockType
{
    use JsonSchemaTrait;

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
        $payload = $this->block['payload'] ?? '';
        $decoded = $this->decodePayloadString($payload);
        return $decoded;
    }

    public function copyPayload(string $rangeId = ''): array
    {
        $payload = $this->getPayload();

        if (!empty($payload['file_id'])) {
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
        if (empty($payload['file_id']) && empty($payload['folder_id'])) {
            return [];
        }

        if (!empty($payload['file_id'])) {
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
