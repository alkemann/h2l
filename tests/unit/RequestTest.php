<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Request, Route, util\Http
};

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructBlank()
    {
        $ref_class = new \ReflectionClass(Request::class);
        $expected_defaults = [
            'parameters' => [],
            'request' => [],
            'server' => [],
            'get' => [],
            'post' => [],
            'route' => null,
            'code' => null,
            'url' => '',
            'method' => Http::GET,
            'body' => null,
            'meta' => [],
            'headers' => [],
            'options' => [],
            'content_type' => '',
            'accept_type' => 'text/html',
            'content_charset' => 'utf-8',
        ];
        $result = $ref_class->getDefaultProperties();
        $this->assertEquals($expected_defaults, $result);

        $request = new Request();
        $properties = $ref_class->getProperties();
        $result = array_reduce($properties, function(array $o, \ReflectionProperty $v) use ($request) {
            $v->setAccessible(true);
            $o[$v->getName()] = $v->getValue($request);
            return $o;
        }, []);
        $this->assertEquals($expected_defaults, $result);
    }

    public function testConstructPattern()
    {
        /**
         * @var $route Route
         */
        $route = $this->getMockBuilder(Route::class)
            ->disableOriginalConstructor()
            ->setMethods(['parameters', 'url'])
            ->getMock();
        $route->expects($this->once())->method('parameters')->willReturn(['city' => 'oslo']);

        $server_params = [
            'HTTP_ACCEPT' => 'application/json;q=0.9',
            'HTTP_CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'HOME' => '/var/www',
            'REQUEST_METHOD' => 'POST'
        ];

        $request = (new Request)
            ->withGetData(['token' => 'hash123'])
            ->withServerParams($server_params)
            ->withRequestParams(['url' => '/place/oslo', 'token' => 'hash123', 'weather' => 'nice'])
            ->withPostData(['weather' => 'nice'])
            ->withRoute($route);
        ;

        $expected = [
            'parameters' => ['city' => 'oslo'],
            'request' => ['token' => 'hash123', 'weather' => 'nice'],
            'server' => $server_params,
            'get' => ['token' => 'hash123'],
            'post' => ['weather' => 'nice'],
            'route' => $route,
            'code' => null,
            'url' => '/place/oslo',
            'method' => Http::POST,
            'body' => null,
            'meta' => [],
            'headers' => ['Accept' => 'application/json;q=0.9', 'Content-Type' => 'application/x-www-form-urlencoded'],
            'options' => [],
            'content_type' => 'application/x-www-form-urlencoded',
            'accept_type' => 'application/json',
            'content_charset' => 'utf-8',
        ];
        $properties = (new \ReflectionClass(Request::class))->getProperties();
        $result = array_reduce($properties, function(array $o, \ReflectionProperty $v) use ($request) {
            $v->setAccessible(true);
            $o[$v->getName()] = $v->getValue($request);
            return $o;
        }, []);
        $this->assertEquals($expected, $result);

        $r2 = $request->withUrlParams(['city' => 'Bergen', 'weather' => 'rain']);
        $this->assertNotSame($request, $r2);

        $this->assertEquals(['city' => 'Bergen', 'weather' => 'rain'], $r2->getUrlParams());
        $this->assertEquals(['token' => 'hash123'], $r2->getGetData());
        $this->assertEquals(['weather' => 'nice'], $r2->getPostData());
        $this->assertEquals(['token' => 'hash123', 'weather' => 'nice'], $r2->getRequestParams());
        $this->assertEquals($server_params, $r2->getServerParams());
        $this->assertEquals('/place/oslo', $r2->url());
        $this->assertEquals(['Accept' => 'application/json;q=0.9', 'Content-Type' => 'application/x-www-form-urlencoded'], $r2->headers());
        $this->assertEquals('application/json;q=0.9', $r2->header('Accept'));
        $this->assertEquals('utf-8', $r2->charset());
        $this->assertEquals(Http::CONTENT_FORM, $r2->contentType());
        $this->assertEquals(Http::CONTENT_JSON, $r2->acceptType());
        $this->assertEquals(Http::POST, $r2->method());
    }

    public function testDefailtServerParamsXML()
    {
        $request = (new Request)
            ->withServerParams([
                'HTTP_ACCEPT' => 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8'
            ]);
        $this->assertEquals(Http::CONTENT_HTML, $request->acceptType());
    }
    public function testAlternativeServerParamsXML()
    {
        $request = (new Request)
            ->withServerParams([
                'HTTP_CONTENT_TYPE' => 'application/xml; charset=utf-8',
                'HTTP_ACCEPT' => 'application/xml;q=0.9, */*;q=0.8'
            ]);
        $this->assertEquals(Http::CONTENT_XML, $request->acceptType());
        $this->assertEquals(Http::CONTENT_XML, $request->contentType());
    }
}
