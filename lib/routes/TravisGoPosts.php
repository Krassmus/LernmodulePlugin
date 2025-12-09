<?php

namespace LernmodulePlugin\routes;

use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\SormCRUDController;
use lib\authorities\TravisGoPostAuthority;
use Psr\Http\Message\ServerRequestInterface as Request;

final class TravisGoPosts extends SormCRUDController
{

    protected function getSORMClassName(): string
    {
        return TravisGoPost::class;
    }

    protected function getData(Request $request, array $args, ?SORM $current = null): array
    {
        // TODO: Implement getData() method.
        return [2];
    }

    protected function getAuthority(): ?SORMAuthority
    {
        return new TravisGoPostAuthority();
    }
}
