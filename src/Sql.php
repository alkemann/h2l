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

    private static function tablename(): string
    {
        // TODO get table from model, throw exception
        return 'todos';
    }

    public static function get($id, array $conditions = [], array $options = [])
    {
        if ($conditions) {
            throw new \InvalidArgumentException("Conditions is not implmenented on get");
        }
        $table = static::tablename();
        $conditions['id'] = $id; // TODO apply pk config name
        $result = static::db()->find($table, $conditions, $options);
        if ($result) {
            return new static($result->fetch_assoc());
        }
        return false;
    }

    public static function find(array $conditions = [], array $options = [])
    {
        $table = static::tablename();
        $db = static::db();
        $result = $db->find($table, $conditions, $options);
        while ($row = $result->fetch_assoc()) {
            yield new static($row);
        }
    }

    public function save(array $data = [], array $options = [])
    {
        $table = static::tablename();
        $data = $data + $this->data;
        $db = static::db();
        $conditions = ['id' => $this->id]; // TODO apply pk from config
        $result = $db->update($table, $conditions, $data, $options);
        return $result;
    }
}
