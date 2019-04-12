<?php
/**
 * Created by PhpStorm.
 * User: alek
 * Date: 01/08/2017
 * Time: 14:30
 */

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Environment;

class EnvironmentTest extends \PHPUnit_Framework_TestCase
{
    private static $test_env = null;

    public static function setUpBeforeClass()
    {
        static::$test_env = Environment::grab(Environment::ALL);
    }

    public function setUp()
    {
        Environment::setEnvironment(Environment::DEV);
        Environment::set(static::$test_env, Environment::ALL);
    }

    public function tearDown()
    {
        Environment::set(static::$test_env);
    }

    public function testEnv()
    {
        $expected = Environment::DEV;
        $result = Environment::current();
        $this->assertEquals($expected, $result);

        $expected = Environment::PROD;
        Environment::setEnvironment(Environment::PROD);
        $result = Environment::current();
        $this->assertEquals($expected, $result);
    }

    public function testCustomEnvironment()
    {
        $env = uniqid();
        Environment::setEnvironment($env);
        $result = Environment::current();
        $this->assertEquals($env, $result);
    }

    public function testPutAndGet()
    {
        $env = uniqid();
        Environment::setEnvironment($env);
        $this->assertFalse(Environment::get('winning', false));
        Environment::put('winning', true, Environment::DEV);
        $this->assertFalse(Environment::get('winning', false));
        Environment::put('winning', true, $env);
        $this->assertTrue(Environment::get('winning', false));
        Environment::put('winning', 123);
        $this->assertEquals(123, Environment::get('winning', false));
    }

    public function testDefaultValueWhenNotSet()
    {
        $expected = true;
        $result = Environment::get('debug', true);
        $this->assertEquals($expected, $result);

        $expected = uniqid();
        $result = Environment::get($expected, $expected);
        $this->assertEquals($expected, $result);
    }

    public function testSetAddAndGrab()
    {
        $env = uniqid();
        Environment::setEnvironment($env);
        $this->assertEquals([], Environment::grab());
        Environment::set(['d' => 1, 'a' => 2]);
        $this->assertEquals(['d' => 1, 'a' => 2], Environment::grab());

        Environment::add(['g' => 3, 'a' => 4]);
        $expected = ['g' => 3, 'a' => 4, 'd' => 1];
        $this->assertEquals($expected, Environment::grab());

        Environment::set(['d' => 1, 'a' => 2]);
        $this->assertEquals(['d' => 1, 'a' => 2], Environment::grab());
    }

    public function testAddAll()
    {
        Environment::add(['thang' => 'thong'], Environment::ALL);
        $expected = static::$test_env;
        foreach ($expected as $env => $v) {
            $expected[$env]['thang'] = 'thong';
        }
        $result = Environment::grab(Environment::ALL);
        $this->assertEquals($expected, $result);
    }

    public function testGrabAll()
    {
        Environment::put('other', 'fun');
        $expected = static::$test_env;
        $expected[Environment::DEV]['other'] = 'fun';
        $result = Environment::grab(Environment::ALL);
        $this->assertEquals($expected, $result);
    }

    public function testPutAll()
    {
        $key = uniqid();
        Environment::put($key, 68, Environment::ALL);
        $this->assertEquals(68, Environment::get($key, 12));
        Environment::setEnvironment(Environment::DEV);
        $this->assertEquals(68, Environment::get($key, 12));
        Environment::setEnvironment(Environment::PROD);
        $this->assertEquals(68, Environment::get($key, 12));
    }

    public function testMiddleWareStorage()
    {
        $this->assertEquals([], Environment::middlewares());
        Environment::setEnvironment(Environment::PROD);
        $f1 = function() { return 1; };
        Environment::addMiddle($f1, Environment::DEV);
        $this->assertEquals([], Environment::middlewares());

        $f2 = function() { return 2; };
        Environment::addMiddle($f2, Environment::ALL);

        $f3 = function() { return 3; };
        Environment::addMiddle($f3);

        $f4 = function() { return 4; };
        Environment::addMiddle($f4, Environment::PROD);

        $this->assertEquals([$f2, $f3, $f4], Environment::middlewares());

        Environment::setEnvironment(Environment::DEV);
        $this->assertEquals([$f1, $f2], Environment::middlewares());
    }
}
