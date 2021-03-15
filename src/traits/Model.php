<?php

namespace alkemann\h2l\traits;

use alkemann\h2l\Connections;
use alkemann\h2l\exceptions;
use alkemann\h2l\exceptions\ConfigMissing;
use alkemann\h2l\interfaces\Source;
use Generator;

/**
 * Class Model
 *
 * Use this for prototyping only, use a real ORM for production stud
 *
 * Depends on \alkemann\h2l\Entity trait
 *
 * @property string $pk
 * @property string $connection
 * @property array $fields
 * @property string $table
 * @property array $data
 * @package alkemann\h2l
 */
trait Model
{
    /**
     * @throws ConfigMissing
     */
    public static function db(): Source
    {
        $name = isset(static::$connection) ? static::$connection : 'default';
        return Connections::get($name);
    }

    private static function pk(): string
    {
        return isset(static::$pk) ? static::$pk : 'id';
    }

    public function exists(): bool
    {
        // @TODO set a "read from db" property?
        $pk = static::pk();
        return isset($this->$pk) && $this->$pk;
    }

    private static function table(): string
    {
        if (!isset(static::$table)) {
            throw new ConfigMissing(
                get_called_class() . ' is missing static::$table',
                ConfigMissing::MISSING_TABLE
            );
        }
        return static::$table;
    }

    /**
     * @TODO throw exception instead of returning null of insert failed?
     * @param int|string $id
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $options
     * @return null|object
     */
    public static function get($id, array $conditions = [], array $options = []): ?object
    {
        if (empty($conditions) === false) {
            throw new \InvalidArgumentException("Conditions is not implemented on get");
        }
        $pk = static::pk();
        $conditions[$pk] = $id;
        $result = static::db()->one(static::table(), $conditions, $options);
        if ($result) {
            return new static($result);
        }
        return null;
    }

    /**
     * Find all records matching $conditions, returns a generator
     *
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $options
     * @return Generator
     * @throws ConfigMissing
     */
    public static function find(array $conditions = [], array $options = []): Generator
    {
        $conditions = self::filterByFields($conditions);
        $with = array_key_exists('with', $options) ? (array)$options['with'] : false;
        unset($options['with']);
        $result = static::db()->find(static::table(), $conditions, $options);
        $pk = static::pk();
        $gen = function() use ($result, $pk, $with) {
            foreach ($result as $row) {
                $model = new static($row);
                if ($with) {
                    $model->with(...$with);
                }
                $id = $row[$pk];
                yield $id => $model;
            }
        };
        return $gen();
    }

    /**
     * The implementation of this method will come from the Entity trait.
     *
     * @param string[] ...$relation_names list of names of relationships
     * @return object Instance of class that uses this trait
     */
    abstract public function with(string ...$relation_names): object;
    abstract public function reset(): void;
    /**
     * Update data of the model, return the (updated) dataset of model
     *
     * @param array|null $data
     * @return array
     */
    abstract public function data(array $data = null): array;

    /**
     * Find all records matching `$conditions`, returns an array with key being the pk value
     *
     * @param array<string, mixed> $conditions
     * @param array<string, mixed> $options
     * @return array
     * @throws ConfigMissing
     */
    public static function findAsArray(array $conditions = [], array $options = []): array
    {
        $generator = static::find($conditions, $options);
        return iterator_to_array($generator);
    }

    /**
     * @return array|null
     */
    public static function fields(): ?array
    {
        return isset(static::$fields) ? static::$fields : null;
    }

    /**
     * @param array<string, mixed> $data
     * @return array
     */
    private static function filterByFields(array $data): array
    {
        $fields = static::fields();
        if (empty($fields) === false) {
            $data = array_filter(
                $data,
                static function($key) use ($fields) {
                    return in_array($key, $fields);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $options
     * @return bool
     * @throws ConfigMissing
     */
    public function save(array $data = [], array $options = []): bool
    {
        $pk = static::pk();
        $db = static::db();
        $table = static::table();

        if ($this->exists()) {
            $id = $this->$pk;
            $data = self::filterByFields($data);
            unset($data[$pk]);
            $rows = $db->update($table, [$pk => $id], $data, $options);
            if (!$rows) {
                return false;
            }
        } else {
            $data += $this->data;
            $data = self::filterByFields($data);
            $id = $db->insert($table, $data, $options);
            if (!$id) {
                return false;
            }
        }

        $result = $db->one($table, [$pk => $id]);
        $this->reset();
        $this->data($result);
        return true;
    }

    /**
     * @param array<string, mixed> $options
     * @return bool
     * @throws exceptions\ConfigMissing
     */
    public function delete(array $options = []): bool
    {
        $pk = static::pk();
        return static::db()->delete(static::table(), [$pk => $this->$pk], $options);
    }
}
