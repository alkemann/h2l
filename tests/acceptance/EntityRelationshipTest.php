<?php

namespace alkemann\h2l\tests\acceptance;

use alkemann\h2l\Connections;
use alkemann\h2l\interfaces\Source;
use alkemann\h2l\tests\mocks\relationship\{
    Car, Father, Son
};

class EntityRelationshipTest extends \PHPUnit\Framework\TestCase
{
    public function testBelongsTo()
    {
        $db = $this->getMockBuilder(Source::class)
            ->setMethods(['__construct','one','query','find','update','insert','delete'])
            ->getMock();
        $db->expects($this->exactly(2))
            ->method('one')
            ->with('fathers', ['id' => 20], [])
            ->willReturn(['id' => 20, 'name' => 'Jake', 'job' => 'Captain']);

        Connections::add('EntityRelationshipTest::testBelongsTo', function() use ($db) {
            return $db;
        });
        Father::$connection = 'EntityRelationshipTest::testBelongsTo';
        Son::$connection = 'EntityRelationshipTest::testBelongsTo';

        $son = new Son(['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12]);

        $expected = new Father(['id' => 20, 'name' => 'Jake', 'job' => 'Captain']);
        $result = $son->father();
        $this->assertEquals($expected, $result);
        $result = $son->dad();
        $this->assertEquals($expected, $result);
        $result = $son->dad();
        $this->assertEquals($expected, $result);
    }

    public function testHasMany()
    {
        $db = $this->getMockBuilder(Source::class)
            ->setMethods(['__construct','one','query','find','update','insert','delete'])
            ->getMock();
        $db->expects($this->exactly(2))
            ->method('find')
            ->with('sons', ['father_id' => 20], [])
            ->willReturn(new \ArrayIterator([
                ['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12],
                ['id' => 11, 'father_id' => 20, 'name' => 'Abe', 'age' => 16]
            ]));

        Connections::add('EntityRelationshipTest::testHasMany', function() use ($db) {
            return $db;
        });
        Father::$connection = 'EntityRelationshipTest::testHasMany';
        Son::$connection = 'EntityRelationshipTest::testHasMany';
        $father = new Father(['id' => 20, 'name' => 'Jake', 'job' => 'Captain']);
        $expected = [
            10 => new Son(['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12]),
            11 => new Son(['id' => 11, 'father_id' => 20, 'name' => 'Abe', 'age' => 16])
        ];
        $result = $father->sons();
        $this->assertEquals($expected, $result);
        $result = $father->children();
        $this->assertEquals($expected, $result);
    }

    public function testHasOne()
    {
        $db = $this->getMockBuilder(Source::class)
            ->setMethods(['__construct','one','query','find','update','insert','delete'])
            ->getMock();
        $db->expects($this->once())
            ->method('find')
            ->with('cars', ['owner_id' => 10], ['limit' => 1])
            ->willReturn(new \ArrayIterator([
                ['id' => 30, 'owner_id' => 10, 'brand' => 'Tesla']
            ]));

        Connections::add('EntityRelationshipTest::testHasOne', function() use ($db) {
            return $db;
        });
        Car::$connection = 'EntityRelationshipTest::testHasOne';
        Son::$connection = 'EntityRelationshipTest::testHasOne';
        $son = new Son(['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12]);
        $expected = new Car(['id' => 30, 'owner_id' => 10, 'brand' => 'Tesla']);
        $result = $son->car();
        $this->assertEquals($expected, $result);
    }

    public function testGetWith()
    {
        $db = $this->getMockBuilder(Source::class)
            ->setMethods(['__construct','one','query','find','update','insert','delete'])
            ->getMock();

        $db->expects($this->exactly(2))
            ->method('one')
            ->withConsecutive(
                ['sons', ['id' => 10], []],
                ['fathers', ['id' => 20], []]
            )
            ->willReturnOnConsecutiveCalls(
                ['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12],
                ['id' => 20, 'name' => 'Jake', 'job' => 'Captain']
            );
        $db->expects($this->once())
            ->method('find')
            ->with('cars', ['owner_id' => 10], ['limit' => 1])
            ->willReturn([['id' => 30, 'owner_id' => 10, 'brand' => 'Tesla']]);

        Connections::add('EntityRelationshipTest::testGetWith', function() use ($db) {
            return $db;
        });
        Father::$connection = 'EntityRelationshipTest::testGetWith';
        Son::$connection = 'EntityRelationshipTest::testGetWith';
        Car::$connection = 'EntityRelationshipTest::testGetWith';

        $expected = new Son(['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12]);
        $father = new Father(['id' => 20, 'name' => 'Jake', 'job' => 'Captain']);
        $expected->populateRelation('father', $father);
        $car = new Car(['id' => 30, 'owner_id' => 10, 'brand' => 'Tesla']);
        $expected->populateRelation('car', $car);
        $result = Son::get(10)->with('father', 'car');
        $this->assertEquals($expected, $result);
    }

    public function testFindWith()
    {
        $db = $this->getMockBuilder(Source::class)
            ->setMethods(['__construct','one','query','find','update','insert','delete'])
            ->getMock();

        $db->expects($this->once())
            ->method('find')
            ->with('sons', ['age' => 12], [])
            ->willReturn([
                ['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12],
                ['id' => 11, 'father_id' => 21, 'name' => 'Jack', 'age' => 12]
            ]);

        $db->expects($this->exactly(2))
            ->method('one')
            ->withConsecutive(
                ['fathers', ['id' => 20], []],
                ['fathers', ['id' => 21], []]
            )
            ->willReturnOnConsecutiveCalls(
                ['id' => 20, 'name' => 'Jake', 'job' => 'Captain'],
                ['id' => 21, 'name' => 'Roger', 'job' => 'Chef']
            );

        Connections::add('EntityRelationshipTest::testFindWith', function() use ($db) {
            return $db;
        });
        Father::$connection = 'EntityRelationshipTest::testFindWith';
        Son::$connection = 'EntityRelationshipTest::testFindWith';

        $son = new Son(['id' => 10, 'father_id' => 20, 'name' => 'John', 'age' => 12]);
        $father = new Father(['id' => 20, 'name' => 'Jake', 'job' => 'Captain']);
        $son->populateRelation('father', $father);

        $son2 = new Son(['id' => 11, 'father_id' => 21, 'name' => 'Jack', 'age' => 12]);
        $father2 = new Father(['id' => 21, 'name' => 'Roger', 'job' => 'Chef']);
        $son2->populateRelation('father', $father2);

        $expected = [10 => $son, 11 => $son2];
        $result = Son::findAsArray(['age' => 12], ['with' => 'father']);
        $this->assertEquals($expected, $result);
    }
}
