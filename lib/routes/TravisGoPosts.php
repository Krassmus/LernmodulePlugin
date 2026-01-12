<?php

namespace LernmodulePlugin\routes;

use JsonApi\Errors\AuthorizationFailedException;
use LernmodulePlugin\models\TravisGoPost;
use LernmodulePlugin\models\TravisGoPostType;
use LernmodulePlugin\models\TravisGoVideoType;
use LernmodulePlugin\SORM;
use LernmodulePlugin\SORMAuthority;
use LernmodulePlugin\SormCRUDController;
use LernmodulePlugin\authorities\TravisGoPostAuthority;
use Psr\Http\Message\ServerRequestInterface as Request;

final class TravisGoPosts extends SormCRUDController
{

    protected $allowedIncludePaths = ['user'];
    protected $allowedFilteringParameters = ['video_id', 'video_type'];

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
                'TravisGoVideoType' => TravisGoVideoType::from($this->getAttribute($field, $current ? $current->$field : null))->value,
                'TravisGoPostType' => TravisGoPostType::from($this->getAttribute($field, $current ? $current->$field : 'meta'))->value,
                'date' => $this->getDateAttribute($field),
                'bool' => $this->getBoolAttribute($field, $current ? $current->$field : false),
            };
        }

        return $data;
    }

    protected function validateFilters(): void
    {
        $filters = $this->getFilters();
        if (!array_key_exists('video_id', $filters) || !array_key_exists('video_type', $filters)) {
            throw new AuthorizationFailedException('Must provide video_id and video_type to read comments');
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
        $conditions[] = 'lernmodule_travis_go_posts.video_id = :video_id';
        $parameters[':video_id'] = $filters['video_id'];
        $conditions[] = 'lernmodule_travis_go_posts.video_type = :video_type';
        $parameters[':video_type'] = $filters['video_type'];
        $condition = implode(' AND ', $conditions);
        return [$condition, $parameters];

    }

    protected function getDefaultOrder(): string
    {
        return 'order by start_time asc';
    }

    protected function getAuthority(): ?SORMAuthority
    {
        return new TravisGoPostAuthority();
    }
}
