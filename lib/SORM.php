<?php
namespace LernmodulePlugin;

use DBManager;
use JsonSerializable;
use PDO;
use SimpleORMap;

abstract class SORM extends SimpleORMap implements JsonSerializable
{
    public function jsonSerialize()
    {
        $data = $this->toRawArray();

        if (isset($data['mkdate'])) {
            $data['mkdate'] = $data['mkdate'] ? date('c', $data['mkdate']) : null;
        }
        if (isset($data['chdate'])) {
            $data['chdate'] = $data['chdate'] ? date('c', $data['chdate']) : null;
        }

        $serialized_fields = self::config('serialized_fields') ?? [];
        foreach ($serialized_fields as $field => $type) {
            $data[$field] = $this->getValue($field) ? $this->getValue($field)->getArrayCopy() : null;
        }

        return $data;
    }

    public function getRelations(): array
    {
        $result = [];
        foreach (array_keys($this->relations) as $relation) {
            $options = $this->getRelationOptions($relation);
            $result[$relation] = [
                'class'    => $options['class_name'],
                'type'     => $options['type'],
                'internal' => !empty($options['internal']),
            ];
        }
        return $result;
    }

    /**
     * Returns distinct number of records
     *
     * @param string $sql sql clause to use on the right side of WHERE
     * @param array  $params params for query
     * @return int
     */
    public static function countDistinctBySql(string $sql = '1', array $params = []): int
    {
        $db_table = static::config('db_table');

        $pk_list = implode(',', array_map(
            function ($key) use ($db_table) {
                return "`{$db_table}`.`{$key}`";
            },
            static::config('pk')
        ));


        return (int) DBManager::get()->fetchColumn(
            self::buildSQLQuery($sql, "COUNT(DISTINCT {$pk_list})"),
            $params
        );
    }

    /**
     * returns array of distinct instances of given class filtered by given sql
     *
     * @param string $sql sql clause to use on the right side of WHERE
     * @param array $params parameters for query
     * @return array array of "self" objects
     */
    public static function findDistinctBySQL(string $sql, array $params = []): array
    {
        $query = self::buildSQLQuery($sql, null, 'DISTINCT');

        $stmt = DBManager::get()->prepare($query);
        $stmt->execute($params);

        $record = static::build([], false);

        $ret = [];
        do  {
            $clone = clone $record;
            $stmt->setFetchMode(PDO::FETCH_INTO, $clone);

            if ($clone = $stmt->fetch()) {
                $clone->applyCallbacks('after_initialize');
                $ret[] = $clone;
            }
        } while ($clone);

        return $ret;
    }

    /**
     * Builds the actual sql query used in some SORM operations.
     *
     * @param string $condition WHERE clause of SQL (may include JOINs)
     * @param string|null $to_select What to select (defaults to <db_table>.*)
     * @param string $prefix Optional prefix before $to_select
     * @return string
     */
    public static function buildSQLQuery(
        string $condition,
        string $to_select = null,
        string $prefix = ''
    ): string
    {
        $db_table = static::config('db_table');

        if ($to_select === null) {
            $to_select = "`{$db_table}`.*";
        }

        $has_join = stripos($condition, 'JOIN ');
        if ($has_join === false || $has_join > 10) {
            $condition = 'WHERE ' . $condition;
        }

        return "SELECT {$prefix} {$to_select} FROM `{$db_table}` {$condition}";
    }

    public function hasI18NFields(): bool
    {
        return count($this->getI18NFields()) > 0;
    }

    public function getI18NFields(): array
    {
        return array_keys(static::config('i18n_fields') ?? []);
    }
}
