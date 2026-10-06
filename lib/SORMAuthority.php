<?php
namespace LernmodulePlugin;

use User;

interface SORMAuthority
{
    public function mayCreate(?User $user, SORM $sorm): bool;

    public function mayAccess(?User $user, SORM $sorm): bool;

    public function mayEdit(?User $user, SORM $sorm): bool;

    public function mayDelete(?User $user, SORM $sorm): bool;
}
