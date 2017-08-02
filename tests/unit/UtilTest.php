<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{

    public function testGetArrayValueByKeys()
    {
        $data = [
            'one' => [
                'one_one' => 87,
                'one_two' => [
                    'one_two_one' => 56
                ]
            ],
            'two' => 98,
            'three' => [
                'three_one' => 73
            ]
        ];

        $this->assertEquals(87, Util::getArrayValueByKeys(['one','one_one'], $data));
        $this->assertEquals(56, Util::getArrayValueByKeys(['one','one_two','one_two_one'], $data));
        $this->assertEquals(98, Util::getArrayValueByKeys(['two'], $data));
        $this->assertEquals(73, Util::getArrayValueByKeys(['three','three_one'], $data));
    }

    public function testExceptionWhenNotSet()
    {
        $this->expectException(\OutOfBoundsException::class);
        $data = [];
        Util::getArrayValueByKeys(['one', 'two'], $data);
    }

    public function testgetFromArrayByKey()
    {
        $data = [
            'one' => [
                'one_one' => 87,
                'one_two' => [
                    'one_two_one' => 56
                ]
            ],
            'two' => 98,
            'three' => [
                'three_one' => 73
            ]
        ];

        $this->assertEquals(87, Util::getFromArrayByKey('one.one_one', $data));
        $this->assertEquals(56, Util::getFromArrayByKey('one.one_two.one_two_one', $data));
        $this->assertEquals(98, Util::getFromArrayByKey('two', $data));
        $this->assertEquals(73, Util::getFromArrayByKey('three.three_one', $data));

        $this->assertEquals(56, Util::getFromArrayByKey('one#one_two#one_two_one', $data, '#'));
        $this->assertEquals(56, Util::getFromArrayByKey('one | one_two | one_two_one', $data, ' | '));
    }

    public function testNullWhenNotSet()
    {
        $data = [
            'one' => [
                'one_one' => 87,
                'one_two' => [
                    'one_two_one' => 56
                ]
            ]
        ];

        $this->assertNull(Util::getFromArrayByKey('one.one_two.one_two_two', $data));
    }
}
