<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{Connections, exceptions\ConfigMissing};
use InvalidArgumentException;
use Exception;

class ConnectionsTest extends \PHPUnit\Framework\TestCase
{

    private static $ref_class;
    private static $ref_connections;
    private static $ref_open;
    private static $ref_close;

    public static function setUpBeforeClass(): void
    {
        static::$ref_class = new \ReflectionClass('alkemann\h2l\Connections');
        static::$ref_connections = static::$ref_class->getProperty('connections');
        static::$ref_connections->setAccessible(true);
        static::$ref_open = static::$ref_class->getProperty('open');
        static::$ref_open->setAccessible(true);
        static::$ref_close = static::$ref_class->getProperty('close');
        static::$ref_close->setAccessible(true);
    }

    public function setUp(): void
    {
        static::$ref_connections->setValue([]);
        static::$ref_open->setValue([]);
        static::$ref_close->setValue([]);
    }

    public function testAdd(): void
    {
        Connections::add('test1', function() {});

        $this->assertEquals(false, static::$ref_connections->getValue()['test1']);
    }

    public function testExceptionOnDuplicate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Connections::add('test1', function() {});
        Connections::add('test1', function() {});
    }

    public function testGetOnMissing(): void
    {
        $this->expectException(ConfigMissing::class);
        $this->expectExceptionCode(ConfigMissing::MISSING_CONNECTION);
        $result = Connections::get('i did not make this');
    }

    public function testGetWithConnect(): void
    {
        $status = false; // not connected at start
        $open = function() use (&$status) {
            if ($status) {
                throw new \Exception("Already connected");
            }
            $status = true;
            return "FAKE DB CONNECTION";
        };
        $close = function($db) use (&$status) {
            if (!$status) {
                throw new \Exception("Already closed");
            }
            $status = false;
        };
        Connections::add('tested', $open, $close);

        $this->assertEquals(false, static::$ref_connections->getValue()['tested']);
        $this->assertSame($open, static::$ref_open->getValue()['tested']);
        $this->assertSame($close, static::$ref_close->getValue()['tested']);

        $expected = "FAKE DB CONNECTION";
        $result = Connections::get('tested');
        $this->assertTrue($status);
        $this->assertEquals($expected, $result);
        $this->assertEquals($expected, static::$ref_connections->getValue()['tested']);

        // This should NOT throw MissingConfig
        $result = Connections::get('tested');
        $this->assertEquals($expected, $result);

        Connections::close('tested');
        $this->assertFalse($status);
        $this->assertEquals(false, static::$ref_connections->getValue()['tested']);
    }

    public function testExceptionOnCloseForNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Connections::close('tested');
    }

    public function testExceptionOnCloseForAlreadyClosed(): void
    {
        $this->expectException(Exception::class);
        static::$ref_connections->setValue(['tested' => false]);
        static::$ref_open->setValue(['tested' => function() {}]);
        static::$ref_close->setValue(['tested' => function() {}]);
        Connections::close('tested');
    }

    public function testOkToCallCloseWithoutCloseConfigured(): void
    {
        static::$ref_connections->setValue(['tested' => true]);
        static::$ref_open->setValue(['tested' => function() {}]);
        Connections::close('tested');
        $this->assertTrue(true);
    }
}
