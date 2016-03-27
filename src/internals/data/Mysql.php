<?php

namespace alkemann\h2l\internals\data;

use Exception;
use mysqli;
use alkemann\h2l\Log;

class Mysql {

    protected $_config = [];

    private $mysql;

    public function __construct(array $config = [])
    {

        $defaults = [
            'host' => 'localhost',
            'auto_connect' => true
        ];
        $this->_config = $config + $defaults;

        if ($this->_config['auto_connect']) {
            $this->connect();
        }
    }

    public function connect()
    {
        $host = $this->_config['host'];
        $user = $this->_config['username'] ?? '';
        $pass = $this->_config['password'] ?? '';
        if ($database = $this->_config['database']) {
            $this->mysql = new mysqli($host, $user, $pass, $database);
        } else {
            $this->mysql = new mysqli($host, $user, $pass);
        }
    }

    public function close() {
        $this->mysql->close();
    }

    public function db($database) {
        $result = $this->mysql->select_db($database);
        if (!$result) {
            die($this->mysql->error . " \n Can't select database [$database]");
        }
        return $this;
    }

    public function escape($value) {
        return "'" . $this->mysql->escape_string($value) . "'";
    }

    public function q($query, array $params = []) {
        Log::debug("Query: " . $query);
        $result = $this->mysql->query($query);
        $last_error = $this->mysql->error;
        if ($last_error) {
            Log::error("MYSQL: " . $last_error);
        }
        return $result;
    }

    public function find($table, array $conditions, array $options = []) {
        $options += ['limit' => 99, 'array' => true, 'fields' => false, 'pk' => false];

        $query = "SELECT "
            . $this->fields($options['fields'])
            . " FROM `$table`"
            . $this->where($conditions)
            . $this->options($options);

        return $this->q($query);
    }

    private function fields($fields) {
        if (!$fields) return '*';
        foreach ($fields as &$field) {
            $field = "`" . $this->mysql->escape_string($field) . "`";
        }
        return join(',', $fields);
    }

    private function where(array $conditions) {
        $where = [];
        foreach ($conditions as $field => $value) {
            $field = $this->mysql->escape_string($field);
            $value = $this->mysql->escape_string($value);
            $where[] = "`$field` = '$value'";
        }
       return $where ? " WHERE " . join(' AND ', $where) : "";
    }

    private function options(array $options) {
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


    public function update($table, array $conditions,  array $data) {
        if (!$conditions || !$data) return false;

        $values = [];
        foreach ($data as $field => $value) {
            $field = $this->mysql->escape_string($field);
            $value = $this->mysql->escape_string($value);
            $values[] = "`$field` = '$value'";
        }
        $values[] = "`updated` = NOW()";
        $where = $this->where($conditions);
        if (!$where) {
            Log::error("No where conditions for update!");
            return false;
        }
        $query = "UPDATE `$table` SET " . join(', ', $values). $where;
        $result = $this->q($query);
        if ($result !== true) {
            return false;
        }
        return $this->mysql->affected_rows;
    }

    public function insert($table, array $data) {
        if (!$data) return false;
        $fields = join('`,`', array_keys($data));
        $fields = "`$fields`,`updated`,`created`";
        $values = [];
        foreach ($data as $value) {
            $values[] = $this->mysql->escape_string($value);
        }
        $values = join("','", $values);
        $values = "'$values',NOW(),NOW()";
        $query  = "INSERT INTO `$table` ($fields) VALUES ($values);";
        $result = $this->q($query);
        if ($result !== true) {
            return false;
        }
        return $this->mysql->insert_id;
    }

    public function delete($table, array $conditions) {
        $where = $this->where($conditions);
        if (!$where) {
            Log::error("No where conditions for delete!");
            return false;
        }
        $query = "DELETE FROM `$table`" . $where;
        $result = $this->q($query);
        if ($result !== true) {
            return false;
        }
        return $this->mysql->insert_id;
    }
}
