<?php
namespace LernmodulePlugin;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use Psr\Http\Message\{
    ResponseInterface as Response,
    ServerRequestInterface as Request
};

abstract class SormCRUDController extends JsonApiController
{
    use SORMCrudCallbacksTrait;

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @return class-string
     */
    abstract protected function getSORMClassName(): string;
    abstract protected function getData(Request $request, array $args, ?SORM $current = null): array;

    protected function getAuthority(): ?SORMAuthority
    {
        return null;
    }

    protected function authorizeItem(SORM $item, Request $request): void
    {
        static $action_mapping = [
            'POST'   => 'mayCreate',
            'GET'    => 'mayAccess',
            'PATCH'  => 'mayEdit',
            'DELETE' => 'mayDelete',
        ];

        $authority = $this->getAuthority();
        if ($authority) {
            $method = $action_mapping[$request->getMethod()];
            $user = $this->getUser($request);

            if (!$authority->$method($user, $item)) {
                throw new AuthorizationFailedException();
            }
        }
    }

    protected function requireItem(Request $request, $id = null): SORM
    {
        $item = $this->requireObject($this->getSORMClass(), $id);

        $this->authorizeItem($item, $request);

        return $item;
    }

    public function __invoke(Request $request, Response $response, array $args)
    {
        if (in_array($request->getMethod(), ['PATCH', 'POST'])) {
            $body = (string) $request->getBody();
            if ($body !== '') {
                $this->validate($request, [
                    'is-new' => $request->getMethod() === 'POST',
                ]);
            }
        }

        $this->validateFilters();

        if ($request->getMethod() === 'POST') {
            return $this->create($request, $response, $args);
        }

        if ($request->getMethod() === 'GET') {
            return $this->restore($request, $response, $args);
        }

        if ($request->getMethod() === 'PATCH') {
            return $this->update($request, $response, $args);
        }

        if ($request->getMethod() === 'DELETE') {
            return $this->delete($request, $response, $args);
        }

        throw new BadRequestException('Not a valid method for this route');
    }

    protected function performCreate(Request $request, Response $response, array $args): SORM
    {
        $item = $this->requireItem($request);
        $data = $this->getData($request, $args);
        return $this->updateData($item, $data);
    }

    protected function create(Request $request, Response $response, array $args): Response
    {
        $item = $this->performCreate($request, $response, $args);
        return $this->getCreatedResponse($item);
    }

    protected function restore(Request $request, Response $response, array $args): Response
    {
        /** @var SORM $class_name */
        $class_name = $this->getSORMClass();

        if (isset($args['id'])) {
            $item = $this->requireItem($request, $args['id']);
            return $this->getContentResponse($item);
        }

        [$condition, $parameters] = $this->getConditionAndParameters($this->getFilters());
        [$offset, $limit] = $this->getOffsetAndLimit();

        $count = $class_name::countDistinctBySql($condition, $parameters);
        $items = $class_name::findDistinctBySQL(
            "{$condition} {$this->getDefaultOrder()} LIMIT {$offset}, {$limit}",
            $parameters
        );

        foreach ($items as $item) {
            $this->authorizeItem($item, $request);
        }
        return $this->getPaginatedContentResponse($items, $count);
    }

    protected function performUpdate(Request $request, Response $response, array $args): SORM
    {
        $item = $this->requireItem($request, $args['id']);
        return $this->updateData($item, $this->getData($request, $args, $item));
    }

    protected function update(Request $request, Response $response, array $args): Response
    {
        $item = $this->performUpdate($request, $response, $args);
        return $this->getContentResponse($item);
    }

    protected function delete(Request $request, Response $response, array $args): Response
    {
        $this->requireItem($request, $args['id'])->delete();

        return $this->getCodeResponse(204);
    }

    protected function validateFilters(): void
    {
    }

    protected function getFilters(): array
    {
        return [];
    }

    protected function getConditionAndParameters(array $filters): array
    {
        return ['1', []];
    }

    protected function getDefaultOrder(): string
    {
        return '';
    }

    protected function getSORMClass(): string
    {
        $class_name = $this->getSORMClassName();
        if (!is_a($class_name, SORM::class, true)) {
            throw new BadRequestException('Only SORM classes may be used with the crud controller');
        }

        return $class_name;
    }

    /**
     * @template T of SORM
     *
     * @param T  $sorm
     * @param array $data
     *
     * @return T
     */
    final protected function updateData(SORM $sorm, array $data): SORM
    {
        $sorm = $this->beforeStore($sorm, $data);

        foreach ($data as $key => $value) {
            // TODO SORM akzeptiert aktuell nur Felder vom Objekt, andere Werte mitschicken wäre noch gut
            $sorm->setValue($key, $value);
        }
        $sorm->store();

        return $this->afterStore($sorm, $data);
    }

    protected function getOffsetAndLimit($offsetDefault = 0, $limitDefault = 30): array
    {
        if (func_num_args() < 2) {
            $limitDefault = 128;
        }

        return parent::getOffsetAndLimit($offsetDefault, $limitDefault);
    }
}
