<?php

namespace LernmodulePlugin\models;
use LernmodulePlugin\SORM;

/**
 * @property string $id
 * @property string $description
 * @property string $mk_user_id
 * @property string $mkdate
 * @property string $chdate
 * @property int $annotation_type
 */
class VideoAnnotation extends SORM {

    public const TYPE_META = 1;
    public const TYPE_IMAGE = 2;
    public const TYPE_AUDIO = 3;
    public const TYPE_TEXT = 4;

    protected static function configure($config = []) {
        $config['db_table'] = 'lernmodule_video_annotations';
    }
}
