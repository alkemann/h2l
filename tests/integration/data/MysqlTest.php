<?php

namespace alkemann\h2l\tests\integration\data;

use alkemann\h2l\exceptions\ConnectionError;
use alkemann\h2l\data\PDO;
use PDO as _PDO;
use PDOException;

class MysqlTest extends \PHPUnit\Framework\TestCase
{
    private static $config = [];
    /**
     * @var \ReflectionMethod
     */
    private static $handler_method;

    public static function setUpBeforeClass(): void
    {
        if (extension_loaded('pdo_mysql') === false) {
            self::markTestSkipped("PHP extension 'pdo_mysql' not installed");
        }
        $f = dirname(dirname(__DIR__)) . '/config/pdo_mysql_connection.php';
        if (file_exists($f) == false) {
            self::markTestSkipped("Missing [ {$f} ] config file");
        }
        self::$config = $c = include($f);

        static::$handler_method = new \ReflectionMethod(PDO::class, 'handler');
        static::$handler_method->setAccessible(true);
        $host = $c['host'];
        $db = $c['db'];
        $user = $c['user'] ?? null;
        $pass = $c['pass'] ?? null;
        try {
            $db = new _PDO("mysql:host={$host};dbname={$db}", $user, $pass);
            $db->query('SHOW TABLES;');
            $db->query('TRUNCATE tests');
        } catch (\PDOException $e) {
            self::markTestSkipped("Connection configured, but connection failed! " . $e->getMessage());
        }
    }

    public function testConnectFail(): void
    {
        $this->expectException(ConnectionError::class);

        $m = new PDO(['host' => 'nope']);
        $h = static::$handler_method->invoke($m);
        $this->assertInstanceOf(_PDO::class, $h);
    }

    public function testConnectSuccess(): void
    {
        $m = new PDO(self::$config);
        $h = static::$handler_method->invoke($m);
        $this->assertInstanceOf(_PDO::class, $h);
    }

    public function testUsage(): void
    {
        $m = new PDO(self::$config);
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
