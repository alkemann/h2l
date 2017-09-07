<?php

namespace alkemann\h2l\tests\unit\util;

use alkemann\h2l\util\Http;

class HttpTest extends \PHPUnit_Framework_TestCase
{
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

        $result = Http::getRequestHeadersFromServerArray($in);
        $this->assertEquals($expected, $result);
    }

    public function testHttpCodeToMessage()
    {
        $this->assertEquals('Unknown', Http::httpCodeToMessage(45345));
        $this->assertEquals('Accepted', Http::httpCodeToMessage(202));
    }

    public function testFileEndingTypes()
    {
        $this->assertEquals('json', Http::fileEndingFromType(Http::CONTENT_JSON));
        $this->assertEquals('xml', Http::fileEndingFromType(Http::CONTENT_XML));
        $this->assertEquals('html', Http::fileEndingFromType(Http::CONTENT_HTML));
        $this->assertEquals('html', Http::fileEndingFromType('text/csv'));
    }
}
