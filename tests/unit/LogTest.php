<?php

namespace alkemann\h2l;

class LogTest extends \PHPUnit_Framework_TestCase
{

    private static $ref_log;
    private static $ref_handlers;

    public static function setUpBeforeClass()
    {
        static::$ref_log = new \ReflectionClass('alkemann\h2l\Log');
        static::$ref_handlers = static::$ref_log->getProperty('handlers');
        static::$ref_handlers->setAccessible(true);
    }

    public function tearDown()
    {
        static::$ref_handlers->setValue([]);
    }

    public function testSettingHandler()
    {
        $cb = function($level, $msg, $context) {};
        Log::handler('test', $cb);
        $result = static::$ref_handlers->getValue();
        $this->assertEquals(['test' => $cb], $result);
    }

    public function testNoHandler()
    {
        $result = static::$ref_handlers->getValue();
        $this->assertEquals([], $result);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCheckHandlerValidityObject()
    {
        Log::handler('not a logg interface', new \stdClass());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCheckHandlerValidityNotCallale()
    {
        Log::handler('not a closure', 'oopsIdidNotMakeThis');
    }

    public function testClosureHandler()
    {
        $out = [];
        $cb = function($level, $msg, $c) use (&$out) {
            $out[] = compact('level', 'msg', 'c');
        };
        Log::handler('test', $cb);

        Log::info("win");
        Log::error("See {:id}", ['id' => 12]);
        Log::log('warning', 'explosion');

        $expected = [
            ['level' => 'info', 'msg' => 'win', 'c' => []], 
            ['level' => 'error', 'msg' => 'See {:id}', 'c' => ['id' => 12]], 
            ['level' => 'warning', 'msg' => 'explosion', 'c' => []]
        ];
        $this->assertEquals($expected, $out);
    }

    public function testObjectHandler()
    {
        $mock = new class // implements \Psr\Log\LoggerInterface
        {
            public $out = [];

            public function __call($name, $arguments)
            {
                $arguments += ["", []];
                $this->log($name, $arguments[0], $arguments[1]);
            }

            public function log($level, $msg = "", $c = [])
            {
                $this->out[] = compact('level', 'msg', 'c');
            }
        };
        Log::handler('object', $mock);

        Log::info("win");
        Log::error("See {:id}", ['id' => 12]);
        Log::log('warning', 'explosion');

        $expected = [
            ['level' => 'info', 'msg' => 'win', 'c' => []], 
            ['level' => 'error', 'msg' => 'See {:id}', 'c' => ['id' => 12]], 
            ['level' => 'warning', 'msg' => 'explosion', 'c' => []]
        ];
        $this->assertEquals($expected, $mock->out);
    }
}