<?php

namespace LernmodulePlugin\routes;

use JsonApi\Errors\AuthorizationFailedException;
use LernmodulePlugin\authorities\TravisGoCommentAuthority;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\SormCRUDController;
use LernmodulePlugin\models\TravisGoComment;
use Psr\Http\Message\ServerRequestInterface as Request;

final class TravisGoComments extends SormCRUDController
{

    protected $allowedIncludePaths = ['user', 'post'];
    protected $allowedFilteringParameters = ['post_id'];

    protected function getSORMClassName(): string
    {
        return TravisGoComment::class;
    }

    protected function getData(Request $request, array $args, ?SORM $current = null): array
    {
        $data = [];
        foreach (TravisGoComment::EDITABLE_FIELDS as $field => $type) {
            $data[$field] = match ($type) {
                'string', 'int', 'float' => $this->getAttribute($field, $current ? $current->$field : ''),
                'date' => $this->getDateAttribute($field),
                'bool' => $this->getBoolAttribute($field, $current ? $current->$field : false),
            };
        }

        return $data;
    }

    protected function validateFilters(): void
    {
        $filters = $this->getFilters();
        if (!array_key_exists('post_id', $filters) ) {
            throw new AuthorizationFailedException('Must provide post_id to read comments');
        }
    }

    protected function getFilters(): array
    {
        $f = $this->getQueryParameters()->getFilteringParameters();

        $filters = [];
        foreach ($this->allowedFilteringParameters as $filteringParameter) {
            if (array_key_exists($filteringParameter, $f)) {
                $filters[$filteringParameter] = $f[$filteringParameter];
            }
        }
        return $filters;
    }

    protected function getConditionAndParameters(array $filters): array
    {
        $conditions = [];
        $parameters = [];
        $conditions[] = 'lernmodule_travis_go_comments.post_id = :post_id';
        $parameters[':post_id'] = $filters['post_id'];
        $condition = implode(' AND ', $conditions);
        return [$condition, $parameters];

    }

    protected function getDefaultOrder(): string
    {
        return 'order by mkdate asc';
    }

    protected function getAuthority(): ?SORMAuthority
    {
        return new TravisGoCommentAuthority();
    }
}
