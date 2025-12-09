<?php

namespace LernmodulePlugin\authorities;

use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use User;

class TravisGoPostAuthority implements SORMAuthority
{

    public function mayCreate(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayCreate() method.
        return true;
    }

    public function mayAccess(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayAccess() method.
        return true;
    }

    public function mayEdit(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayEdit() method.
        return true;
    }

    public function mayDelete(?User $user, SORM $sorm): bool
    {
        // TODO: Implement mayDelete() method.
        return true;
    }
}
