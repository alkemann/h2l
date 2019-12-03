<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Log;
use InvalidArgumentException;

class LogTest extends \PHPUnit\Framework\TestCase
{

    private static $ref_log;
    private static $ref_handlers;

    public static function setUpBeforeClass(): void
    {
        static::$ref_log = new \ReflectionClass('alkemann\h2l\Log');
        static::$ref_handlers = static::$ref_log->getProperty('handlers');
        static::$ref_handlers->setAccessible(true);
    }

    public function tearDown(): void
    {
        static::$ref_handlers->setValue([]);
    }

    public function testSettingHandler(): void
    {
        $cb = function($level, $msg, $context) {};
        Log::handler('test', $cb);
        $result = static::$ref_handlers->getValue();
        $this->assertEquals(['test' => $cb], $result);
    }

    public function testNoHandler(): void
    {
        $result = static::$ref_handlers->getValue();
        $this->assertEquals([], $result);
    }

    public function testCheckHandlerValidityObject(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Log::handler('not a logg interfaces', new \stdClass());
    }

    public function testCheckHandlerValidityNotCallale(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Log::handler('not a closure', 'oopsIdidNotMakeThis');
    }

    public function testClosureHandler(): void
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

    public function testObjectHandler(): void
    {
        $mock = new class // implements \Psr\Log\LoggerInterface
        {
            public $out = [];
            public function log(string $level, string $msg = "", array $c = [])
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
