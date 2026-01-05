<?php

namespace LernmodulePlugin\models;
use LernmodulePlugin\SORM;
use User;

/**
 * @property string $id
 * @property string $video_id
 * @property string $video_type
 * @property string $mk_user_id
 * @property string $mkdate
 * @property string $chdate
 * @property float $start_time
 * @property float $end_time
 * @property string $description
 * @property int $post_type
 */
class TravisGoPost extends SORM {

    public const TYPE_META = 1;
    public const TYPE_IMAGE = 2;
    public const TYPE_AUDIO = 3;
    public const TYPE_TEXT = 4;

    public const EDITABLE_FIELDS = [
        'start_time' => 'float',
        'end_time' => 'float',
        'description' => 'string',
        'post_type' => 'int',
        'video_id' => 'string',
        'video_type' => 'string'
    ];

    protected static function configure($config = []): void {
        $config['db_table'] = 'lernmodule_travis_go_posts';
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'mk_user_id',
        ];
        $config['registered_callbacks']['before_store'][] = function (TravisGoPost $post) {
            $user = User::findCurrent();
            if ($post->isNew()) {
                $post->mk_user_id = $user->id;
            }
        };
        parent::configure($config);
    }
}
