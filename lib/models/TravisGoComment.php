<?php

namespace LernmodulePlugin\models;
use LernmodulePlugin\SORM;
use User;

/**
 * @property string $id
 * @property string $post_id
 * @property string $mk_user_id
 * @property string $mkdate
 * @property string $chdate
 * @property string $contents
 */
class TravisGoComment extends SORM {

    public const EDITABLE_FIELDS = [
        'contents' => 'string',
        'post_id' => 'string',
    ];

    protected static function configure($config = []): void {
        $config['db_table'] = 'lernmodule_travis_go_comments';
        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'mk_user_id',
        ];
        $config['registered_callbacks']['before_store'][] = function (TravisGoComment $comment) {
            $user = User::findCurrent();
            if ($comment->isNew()) {
                $comment->mk_user_id = $user->id;
            }
        };
        parent::configure($config);
    }
}
