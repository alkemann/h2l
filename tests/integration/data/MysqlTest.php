<?php

namespace alkemann\h2l\tests\integration\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\data\Mysql;
use PDO;
use PDOException;

class MysqlTest extends \PHPUnit_Framework_TestCase
{
    private static $config = [];
    private static $handler_method;

    public static function setUpBeforeClass()
    {
        // TODO also skip if missing PDO extension
        $f = dirname(dirname(__DIR__)) . '/config/pdo_mysql_connection.php';
        if (file_exists($f) == false) {
            self::markTestSkipped("Missing [ {$f} ] config file");
        }
        self::$config = $c = include($f);

        static::$handler_method = new \ReflectionMethod(Mysql::class, 'handler');
        static::$handler_method->setAccessible(true);
        $host = $c['host'];
        $db = $c['db'];
        $user = $c['user'];
        $pass = $c['pass'];
        $db = new PDO("mysql:host={$host};dbname={$db}", $user, $pass);
        $db->query('TRUNCATE `tests`;');
    }

    public function testConnectFail()
    {
        $this->expectException(ConnectionError::class);

        $m = new Mysql(['host' => 'nope']);
        $h = static::$handler_method->invoke($m);
        $this->assertTrue($h instanceof PDO);
    }

    public function testConnectSuccess()
    {
        $m = new Mysql(self::$config);
        $h = static::$handler_method->invoke($m);
        $this->assertTrue($h instanceof PDO);
    }

    public function testUsage()
    {
        $m = new Mysql(self::$config);
        $id = $m->insert('tests', ['name' => 'john', 'age' => 38]);
        $this->assertTrue(is_numeric($id));
        $result = $m->one('tests', ['id' => $id]);
        $this->assertTrue($result != false);
        $expected = [
            'id' => $id,
            'name' => 'john',
            'age' => 38
        ];
        $this->assertEquals($expected, $result);

        $jid = $m->insert('tests', ['name' => 'james', 'age' => 18]);
        $cid = $m->insert('tests', ['name' => 'cindy', 'age' => 18]);

        $result = $m->find('tests', ['age' => 18]);
        $this->assertTrue($result instanceof \Traversable);

        $expected = [
            ['id' => $jid, 'name' => 'james', 'age' => 18],
            ['id' => $cid, 'name' => 'cindy', 'age' => 18]
        ];
        $result = iterator_to_array($result);
        $this->assertEquals($expected, $result);

        $result = $m->delete('tests', ['name' => 'john']);
        $this->assertEquals(1, $result);

        $result = $m->find('tests', ['id' => $id]);
        $this->assertEquals(0, $result->rowCount());
    }
}
