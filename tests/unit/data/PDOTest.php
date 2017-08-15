<?php

namespace alkemann\h2l\tests\unit\data;

use alkemann\h2l\data\PDO;
use alkemann\h2l\tests\mocks\mysql\Statement as MockStatement;

class PDOTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $handler = $this->getMockBuilder('PDO')
            ->disableOriginalConstructor()
            ->setMockClassName('PdoHandler') // Mock class name
            ->setMethods(['query']) // mocked methods
            ->getMock();
        $handler->expects($this->once())->method('query')->will($this->returnValue(new class() { public function fetchAll() { return "QUERIED"; }}));

        $m = new PDO;

        $reflection_prop = new \ReflectionProperty(PDO::class, 'db');
        $reflection_prop->setAccessible(true);
        $reflection_prop->setValue($m, $handler);

        $expected = 'QUERIED';
        $result = $m->query('SELECT * FROM tests;');
        $this->assertEquals($expected, $result);
    }

    public function testFind()
    {
        $ec = function($v) { return sizeof($v) === 0; };
        $r  = [['id' => 12, 'title' => 'Gore', 'status' => 1], ['id' => 15, 'title' => 'Space', 'status' => 1]];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM things WHERE status = :c_status LIMIT :o_offset,:o_limit ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $result = $m->find('things', ['status' => 1], ['limit' => 10, 'offset' => 20]);
        $this->assertTrue($result instanceof \Traversable);
        $this->assertEquals("Iterator", $result->name);
        $expected = $r;
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);

        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $result = $m->find('things', ['status' => 1], ['limit' => 10]);
        $this->assertTrue($result instanceof \Traversable);
        $this->assertEquals("Iterator", $result->name);
        $expected = $r;
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);
    }

    public function testFindNoResults()
    {
        $ec = function() { return false; };
        $mi = new MockStatement($ec, []);
        $eq = 'SELECT * FROM things WHERE nothing = :c_nothing ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $result = $m->find('things', ['nothing' => 'has this']);
        $this->assertTrue($result instanceof \Traversable);
        // Check that we are NOT getting the Mock statement in response:
        $this->assertFalse($result instanceof MockStatement);
        foreach ($result as $key => $value) {
            var_dump([$key => $value]);
        }
        $this->assertEquals([], iterator_to_array($result));
    }

    private function createInstanceWithMockedHandler(string $expected_query, MockStatement $ms)
    {
        $handler = $this->getMockBuilder(PDO::class)
            ->setMethods(['prepare', 'lastInsertId']) // mocked methods
            ->getMock();
        $handler->expects($this->once())
            ->method('prepare')
            ->with($expected_query)
            ->will($this->returnValue($ms));

        $m = new PDO;

        $reflection_prop = new \ReflectionProperty(PDO::class, 'db');
        $reflection_prop->setAccessible(true);
        $reflection_prop->setValue($m, $handler);

        return $m;
    }

    public function testEmptyUpdate()
    {
        $m = new PDO;
        $this->assertEquals(0, $m->update('tab', [], ['status' => 'NEW']));
        $this->assertEquals(0, $m->update('tab', ['id' => 1], []));
    }

    public function testOne()
    {
        $ec = function($v) { return sizeof($v) === 0; };
        $r  = [['id' => 12, 'title' => 'Gore']];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM things WHERE id = :c_id ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = ['id' => 12, 'title' => 'Gore'];
        $result = $m->one('things', ['id' => 12]);
        $this->assertEquals($expected, $result);
    }

    public function testOneNotFound()
    {
        $m = $this->getMockBuilder(PDO::class)
            ->setMethods(['find'])
            ->getMock();
        $m->expects($this->once())
            ->method('find')
            ->with('things', ['id' => 99], [])
            ->will($this->returnValue(new \EmptyIterator));
        $this->assertNull($m->one('things', ['id' => 99]));
    }

    public function testOneFoundMany()
    {
        $this->expectException(\Error::class);

        $m = $this->getMockBuilder(PDO::class)
            ->setMethods(['find'])
            ->getMock();
        $f = function() { return true; };
        $r = [['id' => 1], ['id' => 2]];
        $m->expects($this->once())
            ->method('find')
            ->with('things', ['id' => 99], [])
            ->will($this->returnValue(new MockStatement($f, $r)));
        $this->assertNull($m->one('things', ['id' => 99]));
    }

    public function testUpdate()
    {
        $eq = "UPDATE things SET status = :d_status WHERE id = :c_id ;";
        $ec = function() { return true; };
        $mi = new MockStatement($ec, [1]);
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = 1;
        $result = $m->update('things', ['id' => 12], ['status' => 'DONE']);
        $this->assertEquals($expected, $result);
    }

    public function testInsert()
    {
        $eq = "INSERT INTO things (task, status) VALUES (:d_task, :d_status);";
        $ec = function() { return true; };
        $mi = new MockStatement($ec, [1]);
        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $reflection_prop = new \ReflectionProperty(PDO::class, 'db');
        $reflection_prop->setAccessible(true);
        $handler = $reflection_prop->getValue($m);
        $handler->expects($this->once())
            ->method('lastInsertId')
            ->will($this->returnValue(5));

        $expected = 5;
        $result = $m->insert('things', ['task' => 'Win at TDD', 'status' => 'DONE']);
        $this->assertEquals($expected, $result);
    }

    public function testDelete()
    {
        $eq = "DELETE FROM things WHERE id = :c_id ;";
        $ec = function() { return true; };
        $mi = new MockStatement($ec, [1]);
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = 1;
        $result = $m->delete('things', ['id' => 12]);
        $this->assertEquals($expected, $result);
    }

    public function testEmptyDelete()
    {
        $m = new PDO;
        $this->assertEquals(0, $m->delete('tab', []));
    }
}