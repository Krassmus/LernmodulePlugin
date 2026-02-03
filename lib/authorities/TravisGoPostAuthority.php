<?php

namespace LernmodulePlugin\authorities;

use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use User;

class TravisGoPostAuthority implements SORMAuthority
{

    public function mayCreate(?User $user, TravisGoPost|SORM $sorm): bool
    {
        // Check if user has read access to the video that the post is associated with
        return TravisGoVideoAuthority::mayAccess($user, $sorm->video_id, $sorm->video_type);
    }

    public function mayAccess(?User $user, TravisGoPost|SORM $sorm): bool
    {
        return $this->mayCreate($user, $sorm);
    }

    public function mayEdit(?User $user, TravisGoPost|SORM $sorm): bool
    {
        return $user && $sorm->mk_user_id === $user->id;
    }

    public function mayDelete(?User $user, TravisGoPost|SORM $sorm): bool
    {
        return $this->mayEdit($user, $sorm) || TravisGoVideoAuthority::mayEdit($user, $sorm->video_id, $sorm->video_type);
    }
}
