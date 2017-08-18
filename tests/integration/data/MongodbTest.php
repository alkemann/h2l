<?php

namespace integration\data;

use alkemann\h2l\data\MongoDB as Mongo;
use alkemann\h2l\exceptions\ConnectionError;
use MongoDB\BSON\ObjectID;
use MongoDB\Client;

class MongodbTest extends \PHPUnit_Framework_TestCase
{
    private static $config = [];
    private static $collection_handler;

    public static function setUpBeforeClass()
    {
        if (extension_loaded('mongodb') === false) {
            return self::markTestSkipped("MongoDB driver not installed");
        }
        $f = dirname(dirname(__DIR__)) . '/config/mongo_connection.php';
        if (file_exists($f) == false) {
            return self::markTestSkipped("Missing [ {$f} ] config file");
        }
        self::$config = include($f);

        static::$collection_handler = new \ReflectionMethod(Mongo::class, 'collection');
        static::$collection_handler->setAccessible(true);

        $host = self::$config['host'] ?? 'localhost';
        $port = self::$config['port'] ?? 27017;
        try {
            $mc = new Client("mongodb://{$host}:{$port}");
            $mc->dropDatabase('test');
        } catch (\MongoDB\Driver\Exception\ConnectionTimeoutException $e) {
            return self::markTestSkipped("Connection configured, but connection failed!");
        }
    }

    public function testConnectionFail()
    {
        $this->expectException(ConnectionError::class);
        $m = new Mongo(['host' => 'nope']);
        $col = static::$collection_handler->invoke($m, 'tests');
        $this->assertTrue($col instanceof \MongoDB\Collection);
    }

    public function testConnectSuccess()
    {
        $m = new Mongo(self::$config);
        $h = static::$collection_handler->invoke($m, 'tests');
        $this->assertInstanceOf(\MongoDB\Collection::class, $h);
    }

    public function testUsage()
    {
        $m = new Mongo(self::$config);
        $id = $m->insert('tests', ['name' => 'john', 'age' => 38]);
        $this->assertTrue($id instanceof ObjectID);
        $result = $m->one('tests', ['id' => $id]);
        $this->assertTrue($result != false);
        $expected = [
            'id' => "$id",
            'name' => 'john',
            'age' => 38
        ];
        $this->assertEquals($expected, $result);

        $jid = $m->insert('tests', ['name' => 'james', 'age' => 18]);
        $cid = $m->insert('tests', ['name' => 'cindy', 'age' => 18]);

        $result = $m->find('tests', ['age' => 18]);
        $this->assertTrue($result instanceof \Traversable);

        $expected = [
            ['id' => "$jid", 'name' => 'james', 'age' => 18],
            ['id' => "$cid", 'name' => 'cindy', 'age' => 18]
        ];
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);

        $result = $m->delete('tests', ['name' => 'john']);
        $this->assertEquals(1, $result);

        $result = $m->find('tests', ['id' => $id]);
        $this->assertEquals(0, sizeof(iterator_to_array($result)));
    }
}
