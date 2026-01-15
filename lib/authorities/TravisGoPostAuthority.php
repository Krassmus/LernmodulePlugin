<?php

namespace LernmodulePlugin\authorities;

use Courseware\Block;
use JsonApi\Errors\NotImplementedException;
use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use User;

class TravisGoPostAuthority implements SORMAuthority
{

    public function mayCreate(?User $user, TravisGoPost|SORM $sorm): bool
    {
        // Check if user has read access to the video that the post is associated with
        switch ($sorm->video_type) {
            case 'cw_blocks':
                $block = Block::find($sorm->video_id);
                return $block && \JsonApi\Routes\Courseware\Authority::canShowBlock($user, $block);
            case 'lernmodule_module':
                $modul = \Lernmodul::find($sorm->video_id);
                throw new NotImplementedException('Permissions for lernmodule-plugin module are not yet implemented.');
            default:
                throw new NotImplementedException("Invalid or unimplemented video type: '{$sorm->video_type}'");
        };
    }

    public function mayAccess(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayAccess() method.
        return true;
    }

    public function mayEdit(?User $user, SORM $sorm): bool
    {
        return $this->mayDelete($user, $sorm);
    }

    public function mayDelete(?User $user, TravisGoPost|SORM $sorm): bool
    {
        return $user && $sorm->mk_user_id === $user->id;
    }
}
