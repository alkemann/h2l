<?php

namespace alkemann\h2l\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\Log;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Model\BSONDocument;

class MongoDb implements Source
{
    public static $operators = array(
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
    );

    protected $config = [];
    protected $client = null;

    public function __construct(array $config = [])
    {
        $defaults = [
            'host' => 'localhost',
            'port' => 27017,
            'db' => 'default'
        ];
        $this->config = $config + $defaults;
    }

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

    public static function id(string $id): ObjectID
    {
        return new ObjectID($id);
    }

    /**
     * @throws \Exception if called
     */
    public function query($query, array $params = [])
    {
        throw new \Exception("Query method is not implemented for MongDB");
    }

    public function one(string $collection_name, array $conditions, array $options = []): ?array
    {
        $collection = $this->collection($collection_name);
        $conditions = $this->idReplaceConditions($conditions);
        $result = $collection->findOne($conditions, $options);
        if ($result === null) {
            return null;
        }
        return $this->out($result);
    }

    private function idReplaceConditions(array $conditions): array
    {
        if (array_key_exists('id', $conditions)) {
            $id = new ObjectID($conditions['id']);
            unset($conditions['id']);
            $conditions['_id'] = $id;
        }
        return $conditions;
    }

    // @TODO keep the BSON object?
    private function out(BSONDocument $document): array
    {
        $a = $document->getArrayCopy();
        $a['id'] = "{$document->_id}";
        unset($a['_id']);
        return $a;
    }

    public function find(string $collection_name, array $conditions, array $options = []): \Generator
    {
        $collection = $this->collection($collection_name);
        $conditions = $this->idReplaceConditions($conditions);
        $cursor = $collection->find($conditions, $options);
        foreach ($cursor as $document) {
            yield $this->out($document);
        }
    }

    public function update(string $collection_name, array $conditions, array $data, array $options = []): string
    {
        $collection = $this->collection($collection_name);
        $result = $collection->updateMany($conditions, $data, $options);
        if ($result->isAcknowledged() == false) {
            // Throw exception or error?
            return 0;
        }
        return $result->getModifiedCount();
    }

    public function insert(string $collection, array $data, array $options = []): ?ObjectID
    {
        $collection = $this->collection($collection);
        $result = $collection->insertOne($data, $options);
        if ($result->isAcknowledged() == false) {
            // Throw exception or error?
            return null;
        }
        if ($result->getInsertedCount() != 1) {
            Log::error("Failed to insert!");
            return null;
        }
        return $result->getInsertedId();
    }

    public function delete(string $collection, array $conditions, array $options = []): int
    {
        $collection = $this->collection($collection);
        $result = $collection->deleteMany($conditions, $options);
        if ($result->isAcknowledged() == false) {
            // Throw exception or error?
            return 0;
        }
        return $result->getDeletedCount();
    }
}
