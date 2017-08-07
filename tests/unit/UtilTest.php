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

        $this->assertEquals($data, Util::getArrayValueByKeys([], $data));

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
        $this->assertEquals(['one_two_one' => 56], Util::getFromArrayByKey('one.one_two', $data));
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

    public function testHeaderExtractAndConvert()
    {
        $in = [
            'USER' => 'www-data',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_HOST' => 'localhost:8081',
            'HTTP_USER_AGENT' => 'PostmanRuntime/6.2.5',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_CACHE_CONTROL' => 'no-cache',
            'SCRIPT_FILENAME' => '/var/www/html/webroot/index.php',
            'REDIRECT_STATUS' => '200',
            "CONTENT_LENGTH" => '2048',
            "CONTENT_TYPE" => 'JPEG',
            'SERVER_NAME' =>'',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '172.17.0.7',
            'REMOTE_PORT' => '58518',
            'REMOTE_ADDR' => '172.17.0.1',
            'SERVER_SOFTWARE' => 'nginx/1.13.1',
        ];

        $expected = [
            'Connection' => 'keep-alive',
            'Accept-Encoding' => 'gzip, deflate',
            'Host' => 'localhost:8081',
            'User-Agent' => 'PostmanRuntime/6.2.5',
            'Accept' => 'application/json',
            'Cache-Control' => 'no-cache',
            'Content-Length' => '2048',
            'Content-Type' => 'JPEG'
        ];

        $result = Util::getRequestHeadersFromServerArray($in);
        $this->assertEquals($expected, $result);
    }
}
