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

    /**
     * Check if the given user can access the video indicated by $video_id and $video_type.
     * @param User $user
     * @param string $video_id
     * @param string $video_type
     * @return bool
     */
    public static function canAccessVideo(User $user, string $video_id, string $video_type): bool {
        switch ($video_type) {
            case 'cw_blocks':
                $block = Block::find($video_id);
                return $block && \JsonApi\Routes\Courseware\Authority::canShowBlock($user, $block);
            case 'lernmodule_module':
                $modul = \Lernmodul::find($video_id);
                throw new NotImplementedException('Permissions for lernmodule-plugin module are not yet implemented.');
            default:
                throw new NotImplementedException("Invalid or unimplemented video type: '{$video_type}'");
        }
    }
    public function mayCreate(?User $user, TravisGoPost|SORM $sorm): bool
    {
        if (!$user) {
            return false;
        }
        return self::canAccessVideo($user, $sorm->video_id, $sorm->video_type);
    }

    public function mayAccess(?User $user, SORM $sorm): bool
    {
        return self::mayCreate($user, $sorm);
    }

    public function mayEdit(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayEdit() method.
        return false;
    }

    public function mayDelete(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayDelete() method.
        return false;
    }
}
