<?php

namespace LernmodulePlugin\authorities;

use Courseware\Block;
use JsonApi\Errors\NotImplementedException;
use User;

/**
 * Note: Travis Go videos are stored in one of a couple of database tables depending on
 * whether the interactive video task has been used in the 'Lernmodule' tab or the
 * 'Courseware' tab.
 */
class TravisGoVideoAuthority
{
    public static function mayAccess(?User $user, string $video_id, string $video_type): bool {

        // Check if user has read access to the video that is either in Courseware or Lernmodule.
        switch ($video_type) {
            case 'cw_blocks':
                $block = Block::find($video_id);
                return $block && \JsonApi\Routes\Courseware\Authority::canShowBlock($user, $block);
            case 'lernmodule_module':
                $modul = \Lernmodul::find($video_id);
                return $modul && \Lernmodul::mayAccess($user, $modul);
            default:
                throw new NotImplementedException("Invalid or unimplemented video type: '{$video_type}'");
        }
    }

}
