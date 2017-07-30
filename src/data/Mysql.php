<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use PDO;
use alkemann\h2l\Log;

class Mysql implements Source
{

    /**
     * @var array
     */
    protected $_config = [];

    /**
     * @var PDO
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

        $host = $this->config['host'];
        $db = $this->config['db'];
        $user = $this->config['user'];
        $pass = $this->config['pass'];
        $opts = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        try {
            $this->db = new PDO("mysql:host={$host};dbname={$db}", $user, $pass, $opts);
            // @TODO use this?
            // $this->db->setAttribute( PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            throw new ConnectionError("Unable to connect to $host : $db with user $user");
        }
        return $this->db;
    }

    public function query($query, array $params = [])
    {
        Log::debug("PDO:QUERY [$query]");
        $result = $this->handler()->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
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

    public function find(string $table, array $conditions, array $options = []): \Traversable
    {
        $values = [];
        $where = $this->where($conditions);
        $limit = $this->limit($options);
        $query = "SELECT * FROM `{$table}` {$where}{$limit};";
        $params = $this->boundDebugString($conditions, $options);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":c_{$key}", $value);
        }
        if ($limit) {
            $stmt->bindValue(":o_offset", $options['offset'] ?? 0, PDO::PARAM_INT);
            $stmt->bindValue(":o_limit", $options['limit'], PDO::PARAM_INT);
        }
        if ($stmt->execute() === false) {
            return new \EmptyIterator;
        }

        if ($stmt && $stmt instanceof \PDOStatement) {
            // @codeCoverageIgnoreStart
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            // codeCoverageIgnoreEnd
        }
        return $stmt;
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
        if (array_key_exists('limit', $options)) {
            if (array_key_exists('offset', $options)) {
                $values[] = (int)$options['offset'];
            } else {
                $values[] = 0;
            }
            $values[] = (int)$options['limit'];
            return "LIMIT :o_offset,:o_limit ";
        }
        return "";
    }

    public function update(string $table, array $conditions, array $data, array $options = []): int
    {
        if (!$conditions || !$data) {
            return 0;
        }

        $datasql = $this->data($data);
        $where = $this->where($conditions);
        $query = "UPDATE `{$table}` SET {$datasql} {$where};";

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
        return ($result == true) ? $stmt->rowCount() : 0;
    }

    private function data(array $data): string
    {
        $fun = function ($o, $v) {
            return "{$o}{$v} = :d_{$v}";
        };
        return array_reduce(array_keys($data), $fun, "");
    }

    public function insert(string $table, array $data, array $options = []): ?int
    {
        $keys = implode(', ', array_keys($data));
        $data_phs = ':d_' . implode(', :d_', array_keys($data));
        $query = "INSERT INTO `{$table}` ({$keys}) VALUES ({$data_phs});";
        $params = $this->boundDebugString([], [], $data);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':d_' . $key, $value);
        }
        $result = $stmt->execute();
        return ($result == true) ? $dbh->lastInsertId() : null;
    }

    public function delete(string $table, array $conditions, array $options = []): int
    {
        $where = $this->where($conditions);
        if (empty($conditions) || empty($where)) {
            return 0;
        }

        $limit = $this->limit($options);
        $query = "DELETE FROM `{$table}` {$where}{$limit};";
        $params = $this->boundDebugString($conditions, $options);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":c_{$key}", $value);
        }
        if ($limit) {
            $stmt->bindValue(":o_offset", $options['offset'] ?? 0, PDO::PARAM_INT);
            $stmt->bindValue(":o_limit", $options['limit'], PDO::PARAM_INT);
        }
        $result = $stmt->execute();
        return ($result == true) ? $stmt->rowCount() : 0;
    }
}
