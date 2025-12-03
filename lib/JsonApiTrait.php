<?php

namespace LernmodulePlugin;

use LernmodulePlugin\models\VideoAnnotation;
use lib\routes\VideoAnnotations;
use lib\schemas\VideoAnnotationSchema;
use ReflectionClass;
use ReflectionException;
use Slim\Routing\RouteCollectorProxy;

trait JsonApiTrait
{
    public function registerAuthenticatedRoutes(RouteCollectorProxy $group)
    {
        $trait = $this;

        $group->group('/lernmodule-plugin', function (RouteCollectorProxy $group) use ($trait): void {
            $trait->addSORMCrudPaths($group, VideoAnnotations::class);
        });
    }

    public function registerUnauthenticatedRoutes(RouteCollectorProxy $group)
    {
    }

    /**
     * @return array<class-string<SORM>, class-string<SchemaProvider>>
     */
    public function registerSchemas(): array
    {
        return [
            VideoAnnotation::class => VideoAnnotationSchema::class,
        ];
    }

    /**
     * @throws ReflectionException
     */
    protected function addSORMCrudPaths(RouteCollectorProxy $group, string $class_name): void
    {
        $class_name_without_namespace = (new ReflectionClass($class_name))->getShortName();
        $path = strtokebabcase($class_name_without_namespace);

        $group->map(['GET', 'POST'], "/{$path}", $class_name);
        $group->map(['GET', 'PATCH', 'DELETE'], "/{$path}/{id}", $class_name);
    }
}
