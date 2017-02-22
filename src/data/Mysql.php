<?php

namespace alkemann\h2l\data;

use alkemann\h2l\Log;

/**
 * Class Mysql
 * @package alkemann\h2l\data
 */
class Mysql implements Source
{

    /**
     * @var array
     */
    protected $_config = [];

    /**
     * @var mixed
     */
    private $mysql;

    /**
     * Mysql constructor.
     * @param array $config
     */
    public function __construct(array $config = [])
    {

        $defaults = [
            'mysqli' => null,
        ];
        $this->_config = $config + $defaults;

        $this->mysql = $this->_config['mysqli'];
        unset($this->_config['mysqli']);
    }

    /**
     * Close the database connections
     */
    public function close() : void
    {
        $this->mysql->close();
    }

    /**
     * Select the databaase
     *
     * @param $database
     * @return Mysql ($this)
     */
    private function db($database) : Mysql
    {
        $result = $this->mysql->select_db($database);
        if (!$result) {
            die($this->mysql->error . " \n Can't select database [$database]");
        }
        return $this;
    }

    /**
     * Escapes values
     *
     * @param $value
     * @return string
     */
    private function escape($value) : string
    {
        return "'" . $this->mysql->escape_string($value) . "'";
    }

    /**
     * @param string $query
     * @param array $options
     * @return mixed
     */
    public function query($query, array $options = [])
    {
        Log::debug("Query: " . $query);
        $result = $this->mysql->query($query);
        $last_error = $this->mysql->error;
        if ($last_error) {
            Log::error("MYSQL: " . $last_error);
        }
        return $result;
    }

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return string
     */
    public function find(string $table, array $conditions, array $options = [])
    {
        $options += ['limit' => 99, 'array' => true, 'fields' => false, 'pk' => false];

        $query = "SELECT "
            . $this->fields($options['fields'])
            . " FROM `$table`"
            . $this->where($conditions)
            . $this->options($options);

        return $this->query($query);
    }

    /**
     * @param array|null $fields
     * @return string
     */
    private function fields(?array $fields) : string
    {
        if (!$fields) return '*';
        foreach ($fields as &$field) {
            $field = "`" . $this->mysql->escape_string($field) . "`";
        }
        return join(',', $fields);
    }

    /**
     * @param array $conditions
     * @return string
     */
    private function where(array $conditions) : string
    {
        $where = [];
        foreach ($conditions as $field => $value) {
            $field = $this->mysql->escape_string($field);
            $value = $this->mysql->escape_string($value);
            $where[] = "`$field` = '$value'";
        }
       return $where ? " WHERE " . join(' AND ', $where) : "";
    }

    /**
     * @param array $options
     * @return string
     */
    private function options(array $options) : string
    {
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

    /**
     * @param string $table
     * @param array $conditions
     * @param array $data
     * @param array $options
     * @return int
     */
    public function update(string $table, array $conditions, array $data, array $options = []) : int
    {
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
            return 0;
        }
        $query = "UPDATE `$table` SET " . join(', ', $values). $where;
        $result = $this->query($query);
        if ($result !== true) {
            return 0;
        }
        return $this->mysql->affected_rows;
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $options
     * @return int
     */
    public function insert(string $table, array $data, array $options = []) : int
    {
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
        $result = $this->query($query);
        if ($result !== true) {
            return false;
        }
        return $this->mysql->insert_id;
    }

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return int
     */
    public function delete(string $table, array $conditions, array $options = []) : int
    {
        $where = $this->where($conditions);
        if (!$where) {
            Log::error("No where conditions for delete!");
            return false;
        }
        $query = "DELETE FROM `$table`" . $where;
        $result = $this->query($query);
        if ($result !== true) {
            return false;
        }
        return $this->mysql->insert_id;
    }
}
