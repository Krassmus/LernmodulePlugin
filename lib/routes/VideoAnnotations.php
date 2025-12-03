<?php

namespace lib\routes;

use LernmodulePlugin\models\VideoAnnotation;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\SormCRUDController;
use lib\authorities\VideoAnnotationAuthority;
use Psr\Http\Message\ServerRequestInterface as Request;

final class VideoAnnotations extends SormCRUDController
{

    protected function getSORMClassName(): string
    {
        return VideoAnnotation::class;
    }

    protected function getData(Request $request, array $args, ?SORM $current = null): array
    {
        // TODO: Implement getData() method.
    }

    protected function getAuthority(): ?SORMAuthority
    {
        return new VideoAnnotationAuthority();
    }
}
