<?php

namespace alkemann\h2l;

use mysqli;

trait Sql
{

    private static function db(): mysqli
    {
        $config = connection(CONFIG_PATH . 'connection.php');
        $db     = $config['database'];
        $host   = $config['host'] ?? 'localhost';
        $user   = $config['username'] ?? null;
        $pass   = $config['password'] ?? null;
        return new mysqli($host, $user, $pass, $db);
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
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $query = "SELECT * FROM $table WHERE id = '$id'";
        $result = static::q($query);
        if ($result) {
            return $result->fetch_assoc();
        }
        return false;
    }

    public static function find(array $conditions = [], array $options = [])
    {
        $table = static::tablename();
        $options += ['limit' => 99, 'array' => true, 'fields' => false, 'pk' => false];


        $query = "SELECT "
            . static::fields($options['fields'])
            . " FROM `$table`"
            . static::where($conditions)
            . static::options($options);

        $result = static::execute($query);
        if (!$result) {
            return null;
        }
        while ($row = $result->fetch_assoc()) {
            yield $row;
        }
    }

    private static function escape($mysql, $value) {
        return "'" . $mysql->escape_string($value) . "'";
    }

    private static function q($query, array $params = []) {
        Log::debug("Query: " . $query);

        $mysql = static::db();
        $result = $mysql->query($query);
        $last_error = $mysql->error;
        if ($last_error) {
            Log::error("MYSQL: " . $last_error);
        }
        return $result;
    }

    private static function fields($fields) {
        if (!$fields) return '*';
        foreach ($fields as &$field) {
            $field = "`" . static::escape_string($mysql, $field) . "`";
        }
        return join(',', $fields);
    }

    private static function where(array $conditions) {
        $where = [];
        foreach ($conditions as $field => $value) {
            $field = static::escape_string($mysql, $field);
            $value = static::escape_string($mysql, $value);
            $where[] = "`$field` = '$value'";
        }
       return $where ? " WHERE " . join(' AND ', $where) : "";
    }

    private static function options(array $options) {
        if (!$options) return '';

        $query = '';
        if (isset($options['order'])) {
            if (is_array($options['order'])) {
                $order = [];
                foreach ($options['order'] as $field => $dir) {
                    $order[] = "`$field` " . (strtoupper($dir) == 'ASC' ? 'ASC' : 'DESC');
                }
                $query .= " ORDER BY " . join(' AND ', $order);
            } else {
                $query .= " ORDER BY `{$options['order']}`";
            }
        }

        if (isset($options['limit'])) {
            $query .= " LIMIT {$options['limit']}";
            if (isset($options['offset'])) {
                $query .= ',' . $options['offset'];
            }
        }
        return $query;
    }

    private static function execute($query) {
        Log::debug("Query: " . $query);

        $mysql = static::db();
        $result = $mysql->query($query);
        $last_error = static::mysql->error;
        if ($last_error) {
            Log::error("MYSQL: " . $last_error);
        }
        return $result;
    }
/*
    public function update($table, array $conditions,  array $data) {
        if (!$conditions || !$data) return false;

        $values = [];
        foreach ($data as $field => $value) {
            $field = static::escape_string($mysql, $field);
            $value = static::escape_string($mysql, $value);
            $values[] = "`$field` = '$value'";
        }
        $values[] = "`updated` = NOW()";
        $where = static::where($conditions);
        if (!$where) {
            Log::error("No where conditions for update!");
            return false;
        }
        $query = "UPDATE `$table` SET " . join(', ', $values). $where;
        $result = static::execute($query);
        if ($result !== true) {
            return false;
        }
        return static::mysql->affected_rows;
    }

    public function insert($table, array $data) {
        if (!$data) return false;
        $fields = join('`,`', array_keys($data));
        $fields = "`$fields`,`updated`,`created`";
        $values = [];
        foreach ($data as $value) {
            $values[] = static::escape_string($mysql, $value);
        }
        $values = join("','", $values);
        $values = "'$values',NOW(),NOW()";
        $query  = "INSERT INTO `$table` ($fields) VALUES ($values);";
        $result = static::execute($query);
        if ($result !== true) {
            return false;
        }
        return static::mysql->insert_id;
    }

    public function delete($table, array $conditions) {
        $where = static::where($conditions);
        if (!$where) {
            Log::error("No where conditions for delete!");
            return false;
        }
        $query = "DELETE FROM `$table`" . $where;
        $result = static::execute($query);
        if ($result !== true) {
            return false;
        }
        return static::mysql->insert_id;
    }
*/
}
