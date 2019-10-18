<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\interfaces\Source;
use alkemann\h2l\Log;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Model\BSONDocument;

/**
 * Class MongoDb
 *
 * @package alkemann\h2l\data
 */
class MongoDB implements Source
{
    public static $operators = [
        '$all',
        '$gt',
        '$gte',
        '$in',
        '$lt',
        '$lte',
        '$ne',
        '$nin',
        '$or',
        '$and',
        '$not',
        '$nor',
        '$exists',
        '$mod',
        'type',
        '$elemMatch',
        '$size'
    ];

    protected $config = [];
    protected $client = null;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'host' => 'localhost',
            'port' => 27017,
            'db' => 'default'
        ];
        $this->config = $config + $defaults;
    }

    /**
     * @param string $collection
     * @return Collection
     * @throws alkemann\h2l\exceptions\ConnectionError un unable to connect
     */
    private function collection(string $collection): Collection
    {
        if ($this->client == null) {
            $host = $this->config['host'];
            $port = $this->config['port'];
            $options = $this->config;
            unset($options['host']);
            unset($options['port']);
            unset($options['db']);
            try {
                $this->client = new Client("mongodb://{$host}:{$port}", $options);
                $this->client->listDatabases(); // @TODO skip this extra call to trigger connection error fast?
            } catch (RuntimeException $e) {
                throw new ConnectionError("Unable to connect to {$host}:{$port} : " . $e->getMessage());
            }
        }
        $db = $this->config['db'];
        return $this->client->selectCollection($db, $collection);
    }

    /**
     * @param string $id
     * @return ObjectId
     */
    public static function id(string $id): ObjectId
    {
        return new ObjectId($id);
    }

    /**
     * @param mixed $query
     * @param array $params
     * @throws \Exception if called as not implemented
     */
    public function query($query, array $params = [])
    {
        throw new \Exception("Query method is not implemented for MongDB");
    }

    /**
     * @param string $collection_name
     * @param array $conditions
     * @param array $options
     * @return array|null
     */
    public function one(string $collection_name, array $conditions, array $options = []): ?array
    {
        $collection = $this->collection($collection_name);
        $conditions = $this->idReplaceConditions($conditions);
        $result = $collection->findOne($conditions, $options);
        if ($result === null) {
            return null;
        }
        return $result instanceof BSONDocument ? $this->out($result) : $result;
    }

    /**
     * @param array $conditions
     * @return array
     */
    private function idReplaceConditions(array $conditions): array
    {
        if (array_key_exists('id', $conditions)) {
            $id = new ObjectId($conditions['id']);
            unset($conditions['id']);
            $conditions['_id'] = $id;
        }
        return $conditions;
    }

    /**
     * @TODO keep the BSON object?
     * @param BSONDocument $document
     * @return array
     */
    private function out(BSONDocument $document): array
    {
        $a = $document->getArrayCopy();
        $a['id'] = "{$document->_id}";
        unset($a['_id']);
        return $a;
    }

    /**
     * @param string $collection_name
     * @param array $conditions
     * @param array $options
     * @return \Generator
     */
    public function find(string $collection_name, array $conditions, array $options = []): \Generator
    {
        $collection = $this->collection($collection_name);
        $conditions = $this->idReplaceConditions($conditions);
        $cursor = $collection->find($conditions, $options);
        foreach ($cursor as $document) {
            yield $this->out($document);
        }
    }

    /**
     * @param string $collection_name
     * @param array $conditions
     * @param array $data
     * @param array $options
     * @return int
     */
    public function update(string $collection_name, array $conditions, array $data, array $options = []): int
    {
        $collection = $this->collection($collection_name);
        $result = $collection->updateMany($conditions, $data, $options);
        if ($result->isAcknowledged() === false) {
            // Throw exception or error?
            return 0;
        }
        return $result->getModifiedCount() ?? 0;
    }

    /**
     * @param string $collection_name
     * @param array $data
     * @param array $options
     * @return null|ObjectId
     */
    public function insert(string $collection, array $data, array $options = []): ?ObjectId
    {
        $collection = $this->collection($collection);
        $result = $collection->insertOne($data, $options);
        if ($result->isAcknowledged() === false) {
            // Throw exception or error?
            return null;
        }
        if ($result->getInsertedCount() != 1) {
            Log::error("Failed to insert!");
            return null;
        }
        $id = $result->getInsertedId();
        return $id instanceof ObjectId ? $id : null;
    }

    /**
     * @param string $collection_name
     * @param array $conditions
     * @param array $options
     * @return int
     */
    public function delete(string $collection, array $conditions, array $options = []): int
    {
        $collection = $this->collection($collection);
        $result = $collection->deleteMany($conditions, $options);
        if ($result->isAcknowledged() === false) {
            // Throw exception or error?
            return 0;
        }
        return $result->getDeletedCount();
    }
}
