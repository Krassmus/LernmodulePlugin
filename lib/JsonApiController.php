<?php
namespace LernmodulePlugin;

use Exception;
use I18NString;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use JsonApi\Routes\ValidationTrait;
use Psr\Http\Message\{
    ResponseInterface as Response,
    ServerRequestInterface as Request
};
use SimpleORMap;

abstract class JsonApiController extends \JsonApi\JsonApiController
{
    use ValidationTrait;

    private array $attributes = [];
    private array $relations = [];
    private array $meta = [];

    public function __invoke(Request $request, Response $response, array $args)
    {
        $body = (string) $request->getBody();
        if ($body !== '') {
            $this->validate($request);
        }
    }

    protected function validate(Request $request, $data = null)
    {
        $json = $this->decodeRequestBody($request);
        if (isset($json['data']['attributes'])) {
            $this->attributes = $json['data']['attributes'];
        }
        if (isset($json['data']['relationships'])) {
            $this->relations = $json['data']['relationships'];
        }
        $this->meta = $json['data']['meta'] ?? [];

        if ($error = $this->validateResourceDocument($json, $data)) {
            throw new UnprocessableEntityException($error);
        }

        return $json;
    }

    public function validateResourceDocument($json, $data)
    {
        return null;
    }

    protected function hasAttribute(string $key): bool
    {
        $chunks = explode('.', $key);
        $temp = $this->attributes;

        foreach ($chunks as $chunk) {
            if (!array_key_exists($chunk, $temp)) {
                return false;
            }

            $temp = $temp[$chunk];
        }

        return true;
    }

    protected function getAttribute(string $key, $default = null)
    {
        $chunks = explode('.', $key);
        $temp = $this->attributes;

        foreach ($chunks as $chunk) {
            if (!array_key_exists($chunk, $temp)) {
                return $default;
            }

            $temp = $temp[$chunk];
        }

        return $temp;
    }

    protected function getBoolAttribute(string $key, $default = null): ?bool
    {
        $attribute = $this->getAttribute($key);
        return $attribute === null ? $default : (bool) $attribute;
    }

    protected function getDateAttribute(string $key, bool $null_on_failure = false)
    {
        $value = $this->getAttribute($key);
        $result = strtotime($value);

        if ($result === false && $null_on_failure) {
            return null;
        }

        if ($value === false) {
            throw new Exception("Error parsing date attribute {$key}");
        }

        return $result;
    }

    protected function getI18NAttribute(string $key, $default = null): ?I18NString
    {
        if (!$this->hasAttribute($key)) {
            return $default;
        }

        return new I18NString(
            $this->meta['i18n'][$key][I18NString::getDefaultLanguage()] ?? null,
            $this->meta['i18n'][$key] ?? []
        );
    }

    protected function hasRelation(string $key): bool
    {
        return isset($this->relations[$key]);
    }

    protected function getRelation(string $key, bool $null_on_failure = false): ?array
    {
        if (!$this->hasRelation($key) && $null_on_failure) {
            return null;
        }

        if (!$this->hasRelation($key)) {
            throw new Exception("Relation {$key} is not defined");
        }

        return $this->relations[$key];
    }

    protected function hasRelationData(string $relation, string ...$keys): bool
    {
        if (!$this->hasRelation($relation)) {
            throw new Exception("Relation {$relation} is not defined");
        }

        $relation_data = $this->getRelation($relation);
        if (!isset($relation_data['data'])) {
            throw new Exception("Data for relation {$relation} is malformed (misses data attribute)");
        }

        $relation_data = $relation_data['data'];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $relation_data)) {
                return false;
            }

            $relation_data = $relation_data[$key];
        }

        return true;
    }

    protected function getRelationData(string $relation, string ...$keys)
    {
        if (!$this->hasRelation($relation)) {
            throw new Exception("Relation {$relation} is not defined");
        }

        $relation_data = $this->getRelation($relation);
        if (!isset($relation_data['data'])) {
            throw new Exception("Data for relation {$relation} is malformed (misses data attribute)");
        }

        $relation_data = $relation_data['data'];

        foreach ($keys as $key) {
            if (!array_key_exists($key, $relation_data)) {
                throw new Exception("No valid data found for relation {$relation} and path " . implode('.', $keys));
            }

            $relation_data = $relation_data[$key];
        }

        return $relation_data;
    }

    /**
     * @template T
     * @param class-string<T> $model
     *
     * @return T
     */
    protected function requireObject(string $model, $id = null)
    {
        if ($id === null) {
            return new $model;
        }
        $id = explode(SimpleORMap::ID_SEPARATOR, $id);
        $object = $model::find(count($id) > 1 ? $id : array_shift($id));
        if ($object === null) {
            throw new RecordNotFoundException("No object of type {$model} with id {$id}");
        }

        return $object;
    }

    /**
     * @param string $name
     * @param string $type
     * @param class-string $class
     * @param bool   $required
     *
     * @return bool
     * @throws Exception
     */
    protected function validateRelation(string $name, string $type, string $class, bool $required = false): bool
    {
        if (!is_subclass_of($class, SimpleORMap::class)) {
            throw new Exception("Can only validate SORM classes, {$class} is none");
        }

        if (!$this->hasRelation($name)) {
            if ($required) {
                throw new Exception("Missing relation '{$name}'");
            }
            return true;
        }

        if (!$this->hasRelationData($name)) {
            throw new Exception("Invalid relation '{$name}', data is missing.");
        }

        $data = $this->getRelationData($name);
        if (!array_is_list($data)) {
            $data = [$data];
        }

        foreach ($data as $rel) {
            if (!isset($rel['id'], $rel['type'])) {
                throw new Exception("Invalid relation data for '{$name}', missing either 'id' or 'type'.");
            }

            if ($rel['type'] !== $type) {
                throw new Exception("Invalid relation data for '{$name}', expected type {$type} but got {$rel['type']}.");
            }

            if (!$class::exists($rel['id'])) {
                throw new Exception("Invalid relation data for '{$name}', no {$class} record found for id {$rel['id']}.");
            }
        }

        return true;
    }

    /**
     * @template T of SORM
     *
     * @param string $name
     * @param class-string<T> $class
     *
     * @return SORM|SORM
     * @throws Exception
     */
    protected function resolveRelationData(string $name, string $class): array
    {
        if (!$this->hasRelation($name) || !$this->hasRelationData($name)) {
            throw new Exception("Cannot resolve missing relation '{$name}'");
        }

        $data = $this->getRelationData($name);
        $multiple = true;

        if (!array_is_list($data)) {
            $data = [$data];
            $multiple = false;
        }

        $result = array_map(function ($rel) use ($class) {
            $id = explode( SimpleORMap::ID_SEPARATOR, $rel['id']);
            $id = count($id) > 1 ? $id : $id[0];
            $obj = $class::find($id);
            if (!$obj) {
                $obj = new $class();
            }
            if (!empty($rel['attributes']))
                $obj->setData($rel['attributes']);{
            }
            return $obj;
        }, $data);

        return $multiple ? $result : reset($result);
    }


    protected function resolveArrayParameter(string $value): array
    {
        return explode(',', $value);
    }

    protected function validateBooleanFilter(array $filters, string $key): void
    {
        if (array_key_exists($key, $filters)) {
            if (!in_array($filters[$key], [0, 1, 'no', 'yes', 'false', 'true'])) {
                throw new BadRequestException('Invalid boolean value for filter "' . $key . '".');
            }
        }
    }

    protected function getBooleanFilter(array $filters, string $key, ?bool $default = null): ?bool
    {
        if (!array_key_exists($key, $filters)) {
            return $default;
        }
        return in_array($filters[$key], [1, 'yes', 'true']);
    }
}
