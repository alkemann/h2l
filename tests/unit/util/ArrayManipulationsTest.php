<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\ArrayManipulations;
use InvalidArgumentException;

class ArrayManipulationsTest extends \PHPUnit\Framework\TestCase
{

    public function testGetArrayValueByKeys(): void
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

    public function testExceptionWhenNotSet(): void
    {
        $this->expectException(\OutOfBoundsException::class);
        $data = [];
        ArrayManipulations::getArrayValueByKeys(['one', 'two'], $data);
    }

    public function testGetFromArrayByKey(): void
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

    public function testNullWhenNotSet(): void
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

    public function testOutOfBoundsException(): void
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


    public function testSetByArray(): void
    {
        $data = $expected = [
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
        ArrayManipulations::setArrayValueByKeys(['one','one_two','one_two_one'], 42, $data);
        $expected['one']['one_two']['one_two_one'] = 42;
        $this->assertEquals($expected, $data);

        ArrayManipulations::setArrayValueByKeys(['one','one_three'], 42, $data);
        $expected['one']['one_three'] = 42;
        $this->assertEquals($expected, $data);

        ArrayManipulations::setArrayValueByKeys(['four','four_one','four_one_one'], 420, $data);
        $expected['four'] = ['four_one' => ['four_one_one' => 420]];
        $this->assertEquals($expected, $data);
    }

    public function testSetByKey(): void
    {
        $data = $expected = [
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
        ArrayManipulations::setToArrayByKey('one.one_two.one_two_one', 42, $data);
        $expected['one']['one_two']['one_two_one'] = 42;
        $this->assertEquals($expected, $data);

        ArrayManipulations::setToArrayByKey('one:one_three', 42, $data, ':');
        $expected['one']['one_three'] = 42;
        $this->assertEquals($expected, $data);

        ArrayManipulations::setToArrayByKey('four.four_one.four_one_one', 420, $data);
        $expected['four'] = ['four_one' => ['four_one_one' => 420]];
        $this->assertEquals($expected, $data);
    }

    public function testEmptyKeyStringGet(): void
    {
        $data = ['one' => ['two' => ['three' => 12]]];
        $this->expectException(InvalidArgumentException::class);
        $result = ArrayManipulations::getFromArrayByKey('.', $data, '');
    }

    public function testEmptyKeyStringSet(): void
    {
        $data = ['one' => ['two' => ['three' => 12]]];
        $this->expectException(InvalidArgumentException::class);
        ArrayManipulations::setToArrayByKey('.', 77, $data, '');
    }
}
