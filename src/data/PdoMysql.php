<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use PDO;
use alkemann\h2l\Log;

class PdoMysql implements Source
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

    public function one(string $table, array $conditions, array $options = []) : ?array
    {
        $result = $this->find($table, $conditions, $options);

        if ($result) { // && $result instanceof \PDOStatement
            return $result->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }

    public function find(string $table, array $conditions, array $options = []) : \Traversable
    {
        $values = [];
        $where = $this->where($conditions, $values);
        $limit = $this->limit($options, $values);
        $query = "SELECT * FROM `{$table}` {$where}{$limit};";

        $dbh = $this->handler();
        Log::debug("PDO:QUERY [$query]");
        $stmt = $dbh->prepare($query);
        if ($stmt->execute($values) === false) {
            $g = function() { yield ; };
            return $g();
        }

        if ($stmt && $stmt instanceof \PDOStatement) {
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
        }
        return $stmt;
    }

    private function where(array $conditions, array &$values) : string
    {
        $where = "WHERE ";
        foreach ($conditions as $key => $val) {
            $where .= "$key = ? ";
            $values[] = $val;
        }
        return $where == "WHERE " ? "" : $where;
    }

    private function limit(array $options, array &$values) : string
    {
        if (array_key_exists('limit', $options)) {
            if (array_key_exists('offset', $options)) {
                $values[] = (int) $options['offset'];
            } else {
                $values[] = 0;
            }
            $values[] =  (int) $options['limit'];
            return "LIMIT ?,? ";
        }
        return "";
    }

    public function update(string $table, array $conditions, array $data, array $options = []) : int
    {

    }

    public function insert(string $table, array $data, array $options = [])
    {

    }

    public function delete(string $table, array $conditions, array $options = [])
    {

    }
}
