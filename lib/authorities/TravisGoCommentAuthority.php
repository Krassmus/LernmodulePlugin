<?php

namespace LernmodulePlugin\authorities;

use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\models\TravisGoComment;
use User;

class TravisGoCommentAuthority implements SORMAuthority
{

    public function mayCreate(?User $user, TravisGoComment|SORM $sorm): bool
    {
        $post = TravisGoPost::find($sorm->post_id);
        return $post && (new TravisGoPostAuthority())->mayAccess($user, $post);
    }

    public function mayAccess(?User $user, TravisGoComment|SORM $sorm): bool
    {
        $post = TravisGoPost::find($sorm->post_id);
        return $post && (new TravisGoPostAuthority())->mayAccess($user, $post);
    }

    public function mayEdit(?User $user, TravisGoComment|SORM $sorm): bool
    {
        return $user && $sorm->mk_user_id === $user->id;
    }

    public function mayDelete(?User $user, TravisGoPost|SORM $sorm): bool
    {
        return $this->mayEdit($user, $sorm);
    }
}
