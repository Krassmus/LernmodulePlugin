<?php

namespace LernmodulePlugin\schemas;

use LernmodulePlugin\authorities\TravisGoCommentAuthority;
use LernmodulePlugin\SchemaProvider;

class TravisGoCommentSchema extends SchemaProvider
{
    const TYPE = 'lernmodule-plugin/travis-go-comments';
    public function hasResourceMeta($resource): bool
    {
        return true;
    }
    public function getResourceMeta($resource)
    {
        $parentMeta = parent::getResourceMeta($resource);
        $authority = new TravisGoCommentAuthority();
        $user = $this->currentUser;
        $meta = [
            'permissions' => [
                'mayEdit' => $authority->mayEdit($user, $resource),
                'mayDelete' => $authority->mayDelete($user, $resource),
            ]
        ];
        return array_merge($parentMeta, $meta);
    }
}
