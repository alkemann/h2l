<?php

namespace alkemann\h2l;

class ConnectionsTest extends \PHPUnit_Framework_TestCase
{

    private static $ref_class;
    private static $ref_connections;
    private static $ref_open;
    private static $ref_close;

    public static function setUpBeforeClass()
    {
        static::$ref_class = new \ReflectionClass('alkemann\h2l\Connections');
        static::$ref_connections = static::$ref_class->getProperty('connections');
        static::$ref_connections->setAccessible(true);
        static::$ref_open = static::$ref_class->getProperty('open');
        static::$ref_open->setAccessible(true);
        static::$ref_close = static::$ref_class->getProperty('close');
        static::$ref_close->setAccessible(true);
    }

    public function setUp()
    {
        static::$ref_connections->setValue([]);
        static::$ref_open->setValue([]);
        static::$ref_close->setValue([]);
    }

    public function testAdd()
    {
        Connections::add('test1', function() {});

        $this->assertEquals(false, static::$ref_connections->getValue()['test1']);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnDuplicate()
    {
        Connections::add('test1', function() {});
        Connections::add('test1', function() {});
    }

    /**
     * @expectedException alkemann\h2l\exceptions\ConfigMissing
     */
    public function testGetOnMissing()
    {
        $result = Connections::get('i did not make this');
    }

    public function testGetWithConnect()
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionOnCloseForNotExist()
    {
        Connections::close('tested');
    }

    /**
     * @expectedException Exception
     */
    public function testExceptionOnCloseForAlreadyClosed()
    {
        static::$ref_connections->setValue(['tested' => false]);
        static::$ref_open->setValue(['tested' => function() {}]);
        static::$ref_close->setValue(['tested' => function() {}]);
        Connections::close('tested');
    }

    public function testOkToCallCloseWithoutCloseConfigured()
    {

        static::$ref_connections->setValue(['tested' => true]);
        static::$ref_open->setValue(['tested' => function() {}]);
        Connections::close('tested');
    }
}