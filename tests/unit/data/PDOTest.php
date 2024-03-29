<?php

namespace alkemann\h2l\tests\unit\data;

use alkemann\h2l\data\PDO;
use alkemann\h2l\tests\mocks\mysql\Statement as MockStatement;
use PDOStatement;

class PDOTest extends \PHPUnit\Framework\TestCase
{
    public function testConfigFromUrl(): void
    {
        $url = 'mysql://user:pass@localhost/dbname?sslmode=true&win=1';
        $config = ['url' => $url];
        $pdo = new PDO($config);

        $ref_prop = new \ReflectionProperty(PDO::class, 'config');
        $ref_prop->setAccessible(true);

        $expected = [
            'scheme' => 'mysql',
            'host' => 'localhost',
            'user' => 'user',
            'pass' => 'pass',
            'path' => '/dbname',
            'db' => 'dbname',
            'query' => 'sslmode=true&win=1'
        ];
        $result = $ref_prop->getValue($pdo);
        $this->assertEquals($expected, $result);
    }

    public function testHandlerWithQueryParamOptions(): void
    {
        $pdo_mock = new class()
        {
            public $config;
            public function __construct() { $this->config = func_get_args(); }
        };
        $mock_class =  get_class($pdo_mock);
        $url = 'mysql://user:pass@localhost/dbname?sslmode=true&win=1';
        $config = ['url' => $url];
        $pdo = new PDO($config, $mock_class);

        $ref_method = new \ReflectionMethod(PDO::class, 'handler');
        $ref_method->setAccessible(true);
        $result = $ref_method->invoke($pdo);
        $this->assertInstanceOf($mock_class, $result);

        $expected = [
            "mysql:host=localhost;sslmode=true;win=1;dbname=dbname",
            'user',
            'pass',
            [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                'useUnicode' => true,
                'characterEncoding' => 'UTF-8',
            ]
        ];
        $this->assertEquals($expected, $result->config);
    }

    public function testHandlerWithQueryParamOptionsNoQuery(): void
    {
        $pdo_mock = new class()
        {
            public $config;
            public function __construct() { $this->config = func_get_args(); }
        };
        $mock_class =  get_class($pdo_mock);
        $url = 'mysql://user:pass@localhost/dbname';
        $config = ['url' => $url];
        $pdo = new PDO($config, $mock_class);

        $ref_method = new \ReflectionMethod(PDO::class, 'handler');
        $ref_method->setAccessible(true);
        $result = $ref_method->invoke($pdo);
        $this->assertInstanceOf($mock_class, $result);

        $expected = [
            "mysql:host=localhost;dbname=dbname",
            'user',
            'pass',
            [
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                'useUnicode' => true,
                'characterEncoding' => 'UTF-8',
            ]
        ];
        $this->assertEquals($expected, $result->config);
    }

    /**
     * Constructor can't be mocked?
     *

    public function testConnectionError(): void
    {

        $handler = $this->getMockBuilder(\PDO::class)
            ->disableOriginalConstructor()
            ->setMockClassName('PdoHandler') // Mock class name
            ->setMethods(['__construct', 'query']) // mocked methods
            ->getMock();
        $handler->expects($this->once())->method('__construct')->will($this->throwException(new \PDOException('test')));

        $m = new PDO;

        $result = $m->find('things', ['status' => 1], ['limit' => 10, 'offset' => 20]);
    }

    */

    public function testQuery(): void
    {
        $handler = $this->getMockBuilder(\PDO::class)
            ->disableOriginalConstructor()
            ->setMockClassName('PdoHandler') // Mock class name
            ->getMock();
        $statement = $this->getMockBuilder(PDOStatement::class)
            ->getMock();
        $statement->expects($this->once())
            ->method('fetchAll')
            ->willReturn(["QUERIED"]);

        $handler->expects($this->once())
            ->method('query')
            ->will($this->returnValue($statement));

        $m = new PDO;

        $reflection_prop = new \ReflectionProperty(PDO::class, 'db');
        $reflection_prop->setAccessible(true);
        $reflection_prop->setValue($m, $handler);

        $expected = ['QUERIED'] ;
        $result = $m->query('SELECT * FROM tests;');
        $this->assertEquals($expected, $result);
    }

    public function testFind(): void
    {
        $ec = function($v) { return sizeof($v) === 0; };
        $r  = [['id' => 12, 'title' => 'Gore', 'status' => 1], ['id' => 15, 'title' => 'Space', 'status' => 1]];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM things WHERE status = :c_status LIMIT :o_limit OFFSET :o_offset ;';
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

    public function testFindWithOrder(): void
    {
        $ec = function($v) { return sizeof($v) === 0; };
        $r  = [['id' => 15, 'title' => 'Space', 'status' => 1], ['id' => 12, 'title' => 'Gore', 'status' => 1]];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM things WHERE status = :c_status ORDER BY `id` DESC ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $result = $m->find('things', ['status' => 1], ['order' => '`id` DESC']);

        $this->assertTrue($result instanceof \Traversable);
        $this->assertEquals("Iterator", $result->name);
        $expected = $r;
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);
    }

    public function testFindWithInArray(): void
    {
        $ec = function($v) { return sizeof($v) === 0; };
        $r  = [['id' => 12, 'title' => 'Gore', 'status' => 1], ['id' => 15, 'title' => 'Space', 'status' => 1]];
        $mi = new MockStatement($ec, $r);
        $eq = 'SELECT * FROM things WHERE status IN ( :c_status_1, :c_status_2, :c_status_3 ) ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);

        $result = $m->find('things', ['status' => [1, 2, 3]]);
        $this->assertTrue($result instanceof \Traversable);
        $this->assertEquals("Iterator", $result->name);
        $expected = $r;
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);
    }

    public function testFindWithMultipleConditions(): void
    {
        $pdo = new PDO;
        $ref_method = new \ReflectionMethod(PDO::class, 'where');
        $ref_method->setAccessible(true);
        $expected = 'WHERE id = :c_id AND status = :c_status ';
        $result = $ref_method->invoke($pdo, ['id' => 1, 'status' => 1]);
        $this->assertEquals($expected, $result);
    }

    public function testFindWithArrayConditions(): void
    {
        $pdo = new PDO;
        $ref_method = new \ReflectionMethod(PDO::class, 'where');
        $ref_method->setAccessible(true);
        $expected = 'WHERE status IN ( :c_status_1, :c_status_2, :c_status_3, :c_status_4 ) ';
        $result = $ref_method->invoke($pdo, ['status' => [1, 2, 3, 4]]);
        $this->assertEquals($expected, $result);
    }

    public function testFindNoResults(): void
    {
        $ec = function() { return false; };
        $mi = new MockStatement($ec, []);
        $eq = 'SELECT * FROM things WHERE nothing = :c_nothing ;';
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $result = $m->find('things', ['nothing' => 'has this']);
        $this->assertTrue($result instanceof \Traversable);
        // Check that we are NOT getting the Mock statement in response:
        $this->assertFalse($result instanceof MockStatement);
        $this->assertEquals([], iterator_to_array($result));
    }

    private function createInstanceWithMockedHandler(string $expected_query, MockStatement $ms): PDO
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

    public function testEmptyUpdate(): void
    {
        $m = new PDO;
        $this->assertEquals(0, $m->update('tab', [], ['status' => 'NEW']));
        $this->assertEquals(0, $m->update('tab', ['id' => 1], []));
    }

    public function testOne(): void
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

    public function testOneNotFound(): void
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

    public function testOneFoundMany(): void
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

    public function testUpdate(): void
    {
        $eq = "UPDATE things SET status = :d_status, place = :d_place WHERE id = :c_id ;";
        $ec = function() { return true; };
        $mi = new MockStatement($ec, [1]);
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = 1;
        $result = $m->update('things', ['id' => 12], ['status' => 'DONE', 'place' => 'Oslo']);
        $this->assertEquals($expected, $result);
    }

    public function testInsert(): void
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

    public function testDelete(): void
    {
        $eq = "DELETE FROM things WHERE id = :c_id ;";
        $ec = function() { return true; };
        $mi = new MockStatement($ec, [1]);
        $m = $this->createInstanceWithMockedHandler($eq, $mi);
        $expected = 1;
        $result = $m->delete('things', ['id' => 12]);
        $this->assertEquals($expected, $result);
    }

    public function testEmptyDelete(): void
    {
        $m = new PDO;
        $this->assertEquals(0, $m->delete('tab', []));
    }
}
