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
    static $defaults = [
        Environment::DEV => ['debug' => true],
        Environment::TEST => ['debug' => false],
        Environment::PROD => ['debug' => false],
    ];

    public function setUp()
    {
        Environment::setEnvironment(Environment::DEV);
        Environment::set(static::$defaults, Environment::ALL);
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

    public function testGrabAll()
    {
        Environment::put('other', 'fun');
        $expected = static::$defaults;
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
}
