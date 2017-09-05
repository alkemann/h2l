<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\ArrayManipulations;

class ArrayManipulationsTest extends \PHPUnit_Framework_TestCase
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

        $this->assertEquals($data, ArrayManipulations::getArrayValueByKeys([], $data));

        $this->assertEquals(87, ArrayManipulations::getArrayValueByKeys(['one','one_one'], $data));
        $this->assertEquals(56, ArrayManipulations::getArrayValueByKeys(['one','one_two','one_two_one'], $data));
        $this->assertEquals(98, ArrayManipulations::getArrayValueByKeys(['two'], $data));
        $this->assertEquals(73, ArrayManipulations::getArrayValueByKeys(['three','three_one'], $data));
    }

    public function testExceptionWhenNotSet()
    {
        $this->expectException(\OutOfBoundsException::class);
        $data = [];
        ArrayManipulations::getArrayValueByKeys(['one', 'two'], $data);
    }

    public function testGetFromArrayByKey()
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

        $this->assertEquals(87, ArrayManipulations::getFromArrayByKey('one.one_one', $data));
        $this->assertEquals(56, ArrayManipulations::getFromArrayByKey('one.one_two.one_two_one', $data));
        $this->assertEquals(['one_two_one' => 56], ArrayManipulations::getFromArrayByKey('one.one_two', $data));
        $this->assertEquals(98, ArrayManipulations::getFromArrayByKey('two', $data));
        $this->assertEquals(73, ArrayManipulations::getFromArrayByKey('three.three_one', $data));
        $this->assertEquals(56, ArrayManipulations::getFromArrayByKey('one#one_two#one_two_one', $data, '#'));
        $this->assertEquals(56, ArrayManipulations::getFromArrayByKey('one | one_two | one_two_one', $data, ' | '));
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
        $this->assertNull(ArrayManipulations::getFromArrayByKey('one.one_two.one_two_two', $data));
    }

    public function testOutOfBoundsException()
    {
        $data = [];
        $thrown = false;
        $key = 'one.two.three';
        $keys = explode('.', $key);
        try {
            ArrayManipulations::getArrayValueByKeys($keys, $data);
        } catch (\OutOfBoundsException $e) {
            $thrown = true;
            $expected = "Key [one.two.three] not set in Array\n(\n)\n";
            $result = $e->getMessage();
            $this->assertEquals($expected, $result);
        }
        $this->assertTrue($thrown, "Out of bounds exceptions was not thrown");
    }
}
