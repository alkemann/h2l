<?php

namespace alkemann\h2l\tests\acceptance;

use alkemann\h2l\Connections;
use alkemann\h2l\data\Source;
use alkemann\h2l\tests\mocks\relationship\{Father, Son};

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

}
