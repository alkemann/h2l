<?php

namespace alkemann\h2l\tests\unit\data;

use alkemann\h2l\data\PdoMysql;

class MockStatement implements \Iterator {
    public $name = "Iterator";
    protected $ec = null;
    protected $result = [];
    public function __construct(\closure $execute_check, array $result = []) {
        $this->ec = $execute_check;
        $this->result = $result;
    }
    public function current() { return current($this->result); }
    public function key() { return key($this->result); }
    public function next() { next($this->result); }
    public function rewind() { reset($this->result); }
    public function valid() { return current($this->result) !== false; }
    public function execute($v) { $ec = $this->ec; return $ec($v); }
    public function fetch() { return $this->current(); }
}

class PdoMysqlTest extends \PHPUnit_Framework_TestCase
{
    private static $config = [];

    public function testQuery()
    {
        $handler = $this->getMockBuilder('PDO')
            ->disableOriginalConstructor()
            ->setMockClassName('PdoHandler') // Mock class name
            ->setMethods(['query']) // mocked methods
            ->getMock();
        $handler->expects($this->once())->method('query')->will($this->returnValue(new class() { public function fetchAll() { return "QUERIED"; }}));

        $m = new PdoMysql;

        $reflection_prop = new \ReflectionProperty('alkemann\h2l\data\PdoMysql', 'db');
        $reflection_prop->setAccessible(true);
        $reflection_prop->setValue($m, $handler);

        $expected = 'QUERIED';
        $result = $m->query('SELECT * FROM `tests`;');
        $this->assertEquals($expected, $result);
    }

    public function testFind()
    {
        $ec = function($v) { return sizeof($v) === 3 && $v[0] === 1 && $v[1] == 20 && $v[2] == 10;};
        $r  = [['id' => 12, 'title' => 'Gore', 'status' => 1], ['id' => 15, 'title' => 'Space', 'status' => 1]];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM `things` WHERE status = ? LIMIT ?,? ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $result = $m->find('things', ['status' => 1], ['limit' => 10, 'offset' => 20]);
        $this->assertTrue($result instanceof \Traversable);
        $this->assertEquals("Iterator", $result->name);
        $expected = $r;
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);

    }

    private function createInstanceWithMockedHandler(string $expected_query, MockStatement $ms)
    {
        $handler = $this->getMockBuilder(PDO::class)
            ->setMethods(['prepare']) // mocked methods
            ->getMock();
        $handler->expects($this->once())
            ->method('prepare')
            ->with($expected_query)
            ->will($this->returnValue($ms));

        $m = new PdoMysql;

        $reflection_prop = new \ReflectionProperty('alkemann\h2l\data\PdoMysql', 'db');
        $reflection_prop->setAccessible(true);
        $reflection_prop->setValue($m, $handler);

        return $m;
    }

    public function testOne()
    {
        $ec = function($v) { return sizeof($v) === 1 && $v[0] == 12; };
        $r  = [['id' => 12, 'title' => 'Gore']];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM `things` WHERE id = ? ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = ['id' => 12, 'title' => 'Gore'];
        $result = $m->one('things', ['id' => 12]);
        $this->assertEquals($expected, $result);

    }
}
