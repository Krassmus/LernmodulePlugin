<?php

namespace LernmodulePlugin\schemas;

use LernmodulePlugin\authorities\TravisGoPostAuthority;
use LernmodulePlugin\SchemaProvider;

class TravisGoPostSchema extends SchemaProvider
{
    const TYPE = 'lernmodule-plugin/travis-go-posts';
    public function hasResourceMeta($resource): bool
    {
        return true;
    }
    public function getResourceMeta($resource)
    {
        $parentMeta = parent::getResourceMeta($resource);
        $authority = new TravisGoPostAuthority();
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
