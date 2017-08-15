<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\interfaces\Source;
use alkemann\h2l\Log;
use PDO as _PDO;

class PDO implements Source
{

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var _PDO
     */
    protected $db = null;

    public function __construct(array $config = [])
    {
        $defaults = [
            'host' => 'localhost',
            'db' => 'test',
            'user' => null,
            'pass' => null
        ];
        $this->config = $config + $defaults;
    }

    private function handler() //: PDO
    {
        if ($this->db) {
            return $this->db;
        }

        $type = $this->config['type'] ?? 'mysql';
        $host = $this->config['host'];
        $db = $this->config['db'];
        $user = $this->config['user'];
        $pass = $this->config['pass'];
        $opts = [
            _PDO::ATTR_EMULATE_PREPARES => false,
            _PDO::ATTR_ERRMODE => _PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->db = new _PDO("{$type}:host={$host};dbname={$db}", $user, $pass, $opts);

            // @TODO use this?
            // $this->db->setAttribute( _PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            throw new ConnectionError("Unable to connect to $host : $db with user $user");
        }
        return $this->db;
    }

    public function query($query, array $params = [])
    {
        Log::debug("PDO:QUERY [$query]");
        $result = $this->handler()->query($query);
        return $result->fetchAll(_PDO::FETCH_ASSOC);
    }

    public function one(string $table, array $conditions, array $options = []): ?array
    {
        $result = $this->find($table, $conditions, $options);
        $result = iterator_to_array($result);
        $hits = sizeof($result);
        if ($hits === 0) {
            return null;
        }
        if ($hits > 1) {
            throw new \Error("One request found more than 1 match!");
        }

        return $result[0];
    }

    public function find(string $table, array $conditions, array $options = []): iterable
    {
        $where = $this->where($conditions);
        $limit = $this->limit($options);
        $query = "SELECT * FROM {$table} {$where}{$limit};";
        $params = $this->boundDebugString($conditions, $options);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":c_{$key}", $value);
        }
        $this->bindPaginationToStatement($options, $stmt);
        $result = $stmt->execute();
        if ($result === false) {
            return new \EmptyIterator;
        }
        // @codeCoverageIgnoreStart
        if ($stmt instanceof \PDOStatement) {
            $stmt->setFetchMode(_PDO::FETCH_ASSOC);
        }
        // @codeCoverageIgnoreEnd
        return $stmt;
    }

    private function where(array $conditions): string
    {
        if (empty($conditions)) {
            return "";
        }
        $fun = function ($o, $v) {
            return "{$o}{$v} = :c_{$v}";
        };
        $where = array_reduce(array_keys($conditions), $fun, "");
        return "WHERE {$where} ";
    }

    private function limit(array $options): string
    {
        return array_key_exists('limit', $options) ? "LIMIT :o_offset,:o_limit " : '';
    }

    private function boundDebugString(array $conditions, array $options, array $data = []): string
    {
        $out = [];
        foreach ($conditions as $k => $v) {
            $out[] = "c_{$k}:'{$v}'";
        }
        foreach ($data as $k => $v) {
            $out[] = "d_{$k}:{$v}";
        }
        foreach ($options as $k => $v) {
            $out[] = "o_{$k}:{$v}";
        }
        return join(", ", $out);
    }

    /**
     * @param array $options
     * @param \PDOStatement $stmt
     */
    private function bindPaginationToStatement(array $options, $stmt): void
    {
        if (array_key_exists('limit', $options)) {
            $stmt->bindValue(":o_offset", (int)($options['offset'] ?? 0), _PDO::PARAM_INT);
            $stmt->bindValue(":o_limit", (int)$options['limit'], _PDO::PARAM_INT);
        }
    }

    public function update(string $table, array $conditions, array $data, array $options = []): int
    {
        if (empty($conditions) || empty($data)) {
            return 0;
        }

        $datasql = $this->data($data);
        $where = $this->where($conditions);
        $query = "UPDATE {$table} SET {$datasql} {$where};";

        $params = $this->boundDebugString($conditions, $options, $data);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":d_{$key}", $value);
        }
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":c_{$key}", $value);
        }
        $result = $stmt->execute();
        return ($result === true) ? $stmt->rowCount() : 0;
    }

    private function data(array $data): string
    {
        $fun = function ($o, $v) {
            return "{$o}{$v} = :d_{$v}";
        };
        return (string)array_reduce(array_keys($data), $fun, "");
    }

    public function insert(string $table, array $data, array $options = []): ?string
    {
        $keys = implode(', ', array_keys($data));
        $data_phs = ':d_' . implode(', :d_', array_keys($data));
        $query = "INSERT INTO {$table} ({$keys}) VALUES ({$data_phs});";
        $params = $this->boundDebugString([], [], $data);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':d_' . $key, $value);
        }
        $result = $stmt->execute();
        return ($result === true) ? $dbh->lastInsertId() : null;
    }

    public function delete(string $table, array $conditions, array $options = []): int
    {
        $where = $this->where($conditions);
        if (empty($where)) {
            return 0;
        }
        $limit = $this->limit($options);
        $query = "DELETE FROM {$table} {$where}{$limit};";
        $params = $this->boundDebugString($conditions, $options);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":c_{$key}", $value);
        }
        $this->bindPaginationToStatement($options, $stmt);
        $result = $stmt->execute();
        return ($result === true) ? $stmt->rowCount() : 0;
    }
}