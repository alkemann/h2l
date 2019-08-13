<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Request, Route, util\Http, interfaces\Session as SessionInterface
};

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructBlank()
    {
        $ref_class = new \ReflectionClass(Request::class);
        $expected_defaults = [
            'session' => null,
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
            'page_vars' => [],
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
            'session' => null,
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
            'page_vars' => [],
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

    public function testSession()
    {
        $s = $this->getMockBuilder(SessionInterface::class)
            ->setMethods(['set', 'get', 'startIfNotStarted'])
            ->getMock();
        $s->expects($this->exactly(3))
            ->method('get')
            ->withConsecutive(['ask one'], ['ask two'], ['ask three'])
            ->willReturnOnConsecutiveCalls(
                'one',
                'two',
                null
            );

        $this->assertInstanceOf(SessionInterface::class, $s);
        $request = (new Request)->withSession($s);
        $this->assertEquals('one', $request->session('ask one'));
        $this->assertEquals('two', $request->session('ask two'));
        $this->assertEquals(null, $request->session('ask three'));

        $result = $request->session();
        $this->assertEquals($s, $result);
    }

    public function testDefaultServerParamsXML()
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

    public function testServerParam()
    {
        $request = (new Request)
            ->withServerParams([
                'HOME' => '/var/www',
            ]);
        $this->assertEquals('/var/www', $request->getServerParam('HOME'));
        $this->assertNull($request->getServerParam('THING'));
    }

    public function testFullUrl()
    {
        $request = (new Request)
            ->withRequestParams(['url' => '/places/oslo'])
            ->withServerParams([
                'REQUEST_SCHEME' => 'https',
                'HTTP_HOST' => 'example.com:8080'
            ])
        ;
        $this->assertEquals('https://example.com:8080/places/oslo', $request->fullUrl());
        $this->assertEquals('https://example.com:8080/winning/12', $request->fullUrl('/winning/12'));

        $request = (new Request)
            ->withRequestParams(['url' => 'status'])
            ->withServerParams([
                'REQUEST_SCHEME' => 'https',
                'HTTP_HOST' => 'example.com:8080'
            ])
        ;
        $this->assertEquals('https://example.com:8080/status', $request->fullUrl());
        $this->assertEquals('https://example.com:8080/winning', $request->fullUrl('winning'));
    }

    public function testPageVars()
    {
        $request1 = new Request;
        $this->assertEquals([], $request1->pageVars());

        $vars = ['e' => 1, 'em' => 2];
        $request2 = $request1->withPageVars($vars);
        $this->assertFalse($request1 === $request2);
        $this->assertEquals($vars, $request2->pageVars());
    }
}
