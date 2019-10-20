<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\interfaces\Source;
use alkemann\h2l\Log;
use PDO as _PDO;

/**
 * Class PDO
 *
 * @package alkemann\h2l\data
 */
class PDO implements Source
{
    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var null|_PDO
     */
    protected $db = null;

    private $pdo_class = _PDO::class;

    public function __construct(array $config = [], string $pdo_class = _PDO::class)
    {
        $this->pdo_class = $pdo_class;

        if (count($config) === 1 && array_key_exists('url', $config)) {
            $config = parse_url($config['url']);
            $config['db'] = ltrim($config['path'], '/');
        }

        $defaults = [
            'host' => 'localhost',
            'db' => 'test',
            'user' => null,
            'pass' => null
        ];
        $this->config = $config + $defaults;
    }

    /**
     * @return _PDO
     */
    private function handler(): object
    {
        if ($this->db) {
            return $this->db;
        }

        $scheme = $this->config['scheme'] ?? 'mysql';
        $host = $this->config['host'];
        $db = $this->config['db'];
        $user = $this->config['user'];
        $pass = $this->config['pass'] ?? '';
        $port = ($this->config['port'] ?? false) ? ";port={$this->config['port']}" : '';
        if (empty($this->config['query'])) {
            $modifiers = '';
        } else {
            $modifiers = ';' . str_replace('&', ';', $this->config['query']);
        }
        $opts = [
            _PDO::ATTR_EMULATE_PREPARES => false,
            _PDO::ATTR_ERRMODE => _PDO::ERRMODE_EXCEPTION,

            'useUnicode' => true,
            'characterEncoding' => 'UTF-8',
        ];
        $dsn = "{$scheme}:host={$host}{$port}{$modifiers};dbname={$db}";
        $class = $this->pdo_class;
        try {
            $this->db = new $class($dsn, $user, $pass, $opts);

            // @TODO use this?
            // $this->db->setAttribute( _PDO::ATTR_EMULATE_PREPARES, false);
        } catch (\PDOException $e) {
            throw new ConnectionError("Unable to connect to $host : $db with user $user");
        }
        return $this->db;
    }

    /**
     * @param string $query
     * @param array $params
     * @return array|false false on error
     */
    public function query($query, array $params = [])
    {
        Log::debug("PDO:QUERY [$query]");
        $result = $this->handler()->query($query);
        return $result->fetchAll(_PDO::FETCH_ASSOC);
    }

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return null|array
     * @throws \Error on finding more than 1 match
     */
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

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return iterable
     */
    public function find(string $table, array $conditions, array $options = []): iterable
    {
        $where = $this->where($conditions);
        $limit = $this->limit($options);
        $order = $this->order($options);
        $query = "SELECT * FROM {$table} {$where}{$order}{$limit};";
        $params = $this->boundDebugString($conditions, $options);
        Log::debug("PDO:QUERY [$query][$params]");
        $dbh = $this->handler();
        $stmt = $dbh->prepare($query);
        foreach ($conditions as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $index => $v) {
                    $i = $index + 1;
                    $stmt->bindValue(":c_{$key}_{$i}", $v);
                }
            } else {
                $stmt->bindValue(":c_{$key}", $value);
            }
        }
        $this->bindPaginationToStatement($options, $stmt);
        $result = $stmt->execute();
        if ($result === false) {
            return new \EmptyIterator();
        }
        // @codeCoverageIgnoreStart
        if ($stmt instanceof \PDOStatement) {
            $stmt->setFetchMode(_PDO::FETCH_ASSOC);
        }
        // @codeCoverageIgnoreEnd
        return $stmt;
    }

    /**
     * @param array $conditions
     * @return string
     */
    private function where(array $conditions): string
    {
        if (empty($conditions)) {
            return "";
        }
        $fun = function ($o, $v) use ($conditions) {
            if (is_array($conditions[$v])) {
                $qa = [];
                foreach ($conditions[$v] as $key => $value) {
                    $i = $key + 1;
                    $qa[] = ":c_{$v}_{$i}";
                }
                $qs = join(', ', $qa);
                return "{$o} AND {$v} IN ( $qs )";
            } else {
                return "{$o} AND {$v} = :c_{$v}";
            }
        };
        $where = trim(array_reduce(array_keys($conditions), $fun, ""), ' AND ');
        return "WHERE {$where} ";
    }

    private function limit(array $options): string
    {
        return array_key_exists('limit', $options) ? "LIMIT :o_limit OFFSET :o_offset " : '';
    }

    private function order(array $options): string
    {
        if (array_key_exists('order', $options)) {
            // @TODO Add more protection?
            return "ORDER BY {$options['order']} ";
        }
        return '';
    }

    private function boundDebugString(array $conditions, array $options, array $data = []): string
    {
        $out = [];
        foreach ($conditions as $k => $v) {
            if (is_array($v)) {
                $v = join(',', $v);
            }
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
            $stmt->bindValue(":o_offset", (int) ($options['offset'] ?? 0), _PDO::PARAM_INT);
            $stmt->bindValue(":o_limit", (int) $options['limit'], _PDO::PARAM_INT);
        }
    }

    /**
     * @param string $table
     * @param array $conditions
     * @param array $data
     * @param array $options
     * @return int
     */
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
            return "{$o}, {$v} = :d_{$v}";
        };
        return trim((string) array_reduce(array_keys($data), $fun, ""), ", ");
    }

    /**
     * @param string $table
     * @param array $data
     * @param array $options
     * @return null|string
     */
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

    /**
     * @param string $table
     * @param array $conditions
     * @param array $options
     * @return int
     */
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
