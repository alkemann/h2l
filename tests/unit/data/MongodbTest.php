<?php

namespace alkemann\h2l\tests\unit\data;

use alkemann\h2l\data\MongoDB;
use MongoDB\{
    BSON\ObjectId, Collection, DeleteResult, InsertOneResult, Model\BSONDocument, UpdateResult
};

class MongodbTest extends \PHPUnit\Framework\TestCase
{
    static $ref_client;
    public static function setUpBeforeClass(): void
    {
        if (extension_loaded('mongodb') === false) {
            self::markTestSkipped("MongoDB driver not installed");
            return;
        }
        static::$ref_client = new \ReflectionProperty('alkemann\h2l\data\MongoDB', 'client');
        static::$ref_client->setAccessible(true);
    }

    public function testIdMaker(): void
    {
        $id = MongoDB::id('597dfade74050a000678d7b2');
        $this->assertTrue($id instanceof ObjectId);
    }

    public function testConstruct(): void
    {
        $mongo = new MongoDB();

        $ref_prop = new \ReflectionProperty(MongoDB::class, 'config');
        $ref_prop->setAccessible(true);

        $expected = [
            'host' => 'localhost',
            'port' => 27017,
            'db' => 'default'
        ];
        $config = $ref_prop->getValue($mongo);
        $this->assertEquals($expected, $config);

        $set = ['host' => 'mongo', 'port' => 27007, 'db' => 'bond'];
        $mongo2 = new MongoDB($set);
        $config2 = $ref_prop->getValue($mongo2);
        $this->assertEquals($set, $config2);
    }

    private function mocky(Collection $collection, array $config = []): MongoDB
    {
        $mongo = new MongoDB($config);
        $client = new class($collection){
            private $collection = null;
            public function __construct($collection) { $this->collection = $collection; }
            public function selectCollection(string $db, string $col) { return $this->collection; }
        };
        static::$ref_client->setValue($mongo, $client);
        return $mongo;
    }

    public function testOne(): void
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOne'])
            ->getMock();
        $id = MongoDB::id('597dfade74050a000678d7b2');
        $collection->expects($this->once())->method('findOne')
            ->with(['_id' => $id])
            ->willReturn(new BSONDocument([
                '_id' => $id,
                'name' => 'John'
            ]));

        $mongo = $this->mocky($collection);
        $expected = ['id' => '597dfade74050a000678d7b2', 'name' => 'John'];
        $result = $mongo->one('samling', ['id' => '597dfade74050a000678d7b2']);
        $this->assertEquals($expected, $result);
    }

    public function testOneNotFound(): void
    {
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['findOne'])
            ->getMock();
        $collection->expects($this->once())->method('findOne')
            ->with(['name' => 'James'])
            ->willReturn(null);

        $mongo = $this->mocky($collection);
        $this->assertNull($mongo->one('samling', ['name' => 'James']));
    }

    public function testFind(): void
    {
        $cursor = function() {
            yield new BSONDocument([
                '_id' => new ObjectId('597dfade74050a000678d7b2'),
                'name' => 'John',
                'status' => 'NEW'
            ]);
            yield new BSONDocument([
                '_id' => new ObjectId('597dfade74050a000678d111'),
                'name' => 'Alec',
                'status' => 'NEW'
            ]);
        };
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();
        $collection->expects($this->once())
            ->method('find')
            ->with(['status' => 'NEW'])
            ->willReturn($cursor());

        $mongo = $this->mocky($collection);

        $result = $mongo->find('people', ['status' => 'NEW']);
        $this->assertTrue($result == true);

        $expected = [
            [
                'id' => '597dfade74050a000678d7b2',
                'name' => 'John',
                'status' => 'NEW'
            ],
            [
                'id' => '597dfade74050a000678d111',
                'name' => 'Alec',
                'status' => 'NEW'
            ]
        ];
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);
    }

    public function testInsertOne(): void
    {
        $mongo_result = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getInsertedId', 'getInsertedCount', 'isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(true);
        $mongo_result->expects($this->once())
            ->method('getInsertedId')
            ->willReturn(new ObjectId('597dfade74050a000678d222'));
        $mongo_result->expects($this->once())
            ->method('getInsertedCount')
            ->willReturn(1);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $collection->expects($this->once())
            ->method('insertOne')
            ->with(['name' => 'Dolf', 'status' => 'NEW'], [])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = '597dfade74050a000678d222';
        $result = $mongo->insert('people', ['name' => 'Dolf', 'status' => 'NEW']);
        $this->assertEquals($expected, $result);
    }

    public function testInsertOneNotAcknowledged(): void
    {
        $mongo_result = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(false);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $collection->expects($this->once())
            ->method('insertOne')
            ->with(['name' => 'Dolf', 'status' => 'NEW'], [])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = null;
        $result = $mongo->insert('people', ['name' => 'Dolf', 'status' => 'NEW']);
        $this->assertEquals($expected, $result);
    }


    public function testInsertFailed(): void
    {
        $mongo_result = $this->getMockBuilder(InsertOneResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAcknowledged', 'getInsertedCount'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(true);
        $mongo_result->expects($this->once())
            ->method('getInsertedCount')
            ->willReturn(0);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $collection->expects($this->once())
            ->method('insertOne')
            ->with(['name' => 'Dolf', 'status' => 'NEW'], [])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = null;
        $result = $mongo->insert('people', ['name' => 'Dolf', 'status' => 'NEW']);
        $this->assertEquals($expected, $result);
    }

    public function testUpdate(): void
    {
        $mongo_result = $this->getMockBuilder(UpdateResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getModifiedCount', 'isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(true);
        $mongo_result->expects($this->once())
            ->method('getModifiedCount')
            ->willReturn(3);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $collection->expects($this->once())
            ->method('updateMany')
            ->with(['status' => 'NEW'], ['status' => 'OPEN'])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = 3;
        $result = $mongo->update('people', ['status' => 'NEW'], ['status' => 'OPEN']);
        $this->assertEquals($expected, $result);
    }

    public function testUpdateNotAcknowledged(): void
    {
        $mongo_result = $this->getMockBuilder(UpdateResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(false);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $collection->expects($this->once())
            ->method('updateMany')
            ->with(['status' => 'NEW'], ['status' => 'OPEN'])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = 0;
        $result = $mongo->update('people', ['status' => 'NEW'], ['status' => 'OPEN']);
        $this->assertEquals($expected, $result);
    }

    public function testDelete(): void
    {
        $mongo_result = $this->getMockBuilder(DeleteResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDeletedCount', 'isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(true);
        $mongo_result->expects($this->once())
            ->method('getDeletedCount')
            ->willReturn(1);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteMany'])
            ->getMock();
        $collection->expects($this->once())
            ->method('deleteMany')
            ->with(['status' => 'OLD'], [])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = 1;
        $result = $mongo->delete('people', ['status' => 'OLD']);
        $this->assertEquals($expected, $result);
    }

    public function testDeleteUnacknowledged(): void
    {
        $mongo_result = $this->getMockBuilder(DeleteResult::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAcknowledged'])
            ->getMock();
        $mongo_result->expects($this->once())
            ->method('isAcknowledged')->willReturn(false);
        $collection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteMany'])
            ->getMock();
        $collection->expects($this->once())
            ->method('deleteMany')
            ->with(['status' => 'OLD'], [])
            ->willReturn($mongo_result);

        $mongo = $this->mocky($collection);
        $expected = 0;
        $result = $mongo->delete('people', ['status' => 'OLD']);
        $this->assertEquals($expected, $result);
    }

    public function testQueryException(): void
    {
        $this->expectException(\Exception::class);
        $mongo = new MongoDB();
        $mongo->query('something');
    }
}
