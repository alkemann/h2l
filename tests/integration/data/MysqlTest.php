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
        self::$config = include($f);

        static::$handler_method = new \ReflectionMethod('alkemann\h2l\data\Mysql', 'handler');
        static::$handler_method->setAccessible(true);
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
}
