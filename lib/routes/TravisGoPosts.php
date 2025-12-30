<?php

namespace LernmodulePlugin\routes;

use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\SormCRUDController;
use LernmodulePlugin\authorities\TravisGoPostAuthority;
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
        $data = [];
        foreach (TravisGoPost::EDITABLE_FIELDS as $field => $type) {
            $data[$field] = match ($type) {
                'string', 'int', 'float' => $this->getAttribute($field, $current ? $current->$field : ''),
                'date' => $this->getDateAttribute($field),
                'bool' => $this->getBoolAttribute($field, $current ? $current->$field : false),
            };
        }

        return $data;
    }

    protected function getAuthority(): ?SORMAuthority
    {
        return new TravisGoPostAuthority();
    }
}
