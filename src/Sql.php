<?php

namespace alkemann\h2l;

use alkemann\h2l\internals\data\Mysql;

trait Sql
{
    private static $db;

    private static function db(): Mysql
    {
        if (!static::$db) {
            $config = connection(CONFIG_PATH . 'connection.php');
            static::$db = new Mysql($config);
        }
        return static::$db;
    }

    private static function pk(): string
    {
        return isset(static::$pk) ? static::$pk : 'id';
    }

    private static function tablename(): string
    {
        if (!isset(static::$tablename)) {
            throw new \alkemann\h2l\exceptions\ConfigMissing(get_called_class() . ' is missing static::$tablename');
        }
        return static::$tablename;
    }


    public static function get($id, array $conditions = [], array $options = [])
    {
        if ($conditions) {
            throw new \InvalidArgumentException("Conditions is not implmenented on get");
        }
        $pk = static::pk();
        $conditions[$pk] = $id;
        $result = static::db()->find(static::tablename(), $conditions, $options);
        if ($result && $result->num_rows == 1) {
            return new static($result->fetch_assoc());
        }
        return false;
    }

    public static function find(array $conditions = [], array $options = [])
    {
        $conditions = self::filterByFields($conditions);
        $result = static::db()->find(static::tablename(), $conditions, $options);
        while ($row = $result->fetch_assoc()) {
            yield new static($row);
        }
    }

    protected static function fields()
    {
        return isset(static::$fields) ? static::$fields : false;
    }

    private static function filterByFields(array $data)
    {
        $fields = static::fields();
        if ($fields) {
            $data = array_filter(
                $data,
                function($key) use ($fields) {
                    return in_array($key, $fields);
                },
                ARRAY_FILTER_USE_KEY
            );
        }
        return $data;
    }

    public function save(array $data = [], array $options = [])
    {
        $pk     = static::pk();
        $db     = static::db();
        $table  = static::tablename();

        if ($this->exists()) {
            $id   = $this->$pk;
            $data = self::filterByFields($data);
            $rows = $db->update($table, [$pk => $id], $data, $options);
            if (!$rows) return false;
        } else {
            $data += $this->data;
            $data = self::filterByFields($data);
            $id = $db->insert($table, $data, $options); // todo filter fields of $data
            if (!$id) return false;
        }

        $result = static::db()->find($table, [$pk => $id], ['limit' => 1]);
        $this->reset();
        $this->data($result->fetch_assoc());

        return true;
    }

    public function delete(Entity $entity, array $options = [])
    {
        $pk = static::pk();
        return static::db()->delete(static::tablename(), [$pk => $this->$pk]);
    }
}
