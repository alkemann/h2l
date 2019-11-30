<?php

namespace alkemann\h2l\tests\unit;

use alkemann\h2l\{
    Request, Route, util\Http, interfaces\Session as SessionInterface
};

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructBlank(): void
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

    public function testConstructPattern(): void
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

    public function testSession(): void
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

    public function testNoSessionException(): void
    {
        $request = new Request();
        $this->expectException(\Exception::class);
        $request->session('thing');
    }

    public function testDefaultServerParamsXML(): void
    {
        $request = (new Request)
            ->withServerParams([
                'HTTP_ACCEPT' => 'text/html, application/xhtml+xml, application/xml;q=0.9, */*;q=0.8'
            ]);
        $this->assertEquals(Http::CONTENT_HTML, $request->acceptType());
    }
    public function testAlternativeServerParamsXML(): void
    {
        $request = (new Request)
            ->withServerParams([
                'HTTP_CONTENT_TYPE' => 'application/xml; charset=utf-8',
                'HTTP_ACCEPT' => 'application/xml;q=0.9, */*;q=0.8'
            ]);
        $this->assertEquals(Http::CONTENT_XML, $request->acceptType());
        $this->assertEquals(Http::CONTENT_XML, $request->contentType());
    }

    public function testServerParam(): void
    {
        $request = (new Request)
            ->withServerParams([
                'HOME' => '/var/www',
            ]);
        $this->assertEquals('/var/www', $request->getServerParam('HOME'));
        $this->assertNull($request->getServerParam('THING'));
    }

    public function testFullUrl(): void
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

    public function testPageVars(): void
    {
        $request1 = new Request;
        $this->assertEquals([], $request1->pageVars());

        $vars = ['e' => 1, 'em' => 2];
        $request2 = $request1->withPageVars($vars);
        $this->assertFalse($request1 === $request2);
        $this->assertEquals($vars, $request2->pageVars());
    }

    public function testParamUrlFirst(): void
    {
        $shared = ['city' => 'London', 'id' => 5];
        $r = (new Request())
            ->withGetData(
                ['id' => 15, 'getit' => 'yay'] + $shared)
            ->withPostData(
                ['id' => 25, 'postit' => 'cheers'] + $shared)
            ->withUrlParams(
                ['id' => 35, 'urlit' => 'rock'] + $shared)
            ->withBody(json_encode(
                ['id' => 45, 'jsonit' => 'awesome'] + $shared))
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"'])
            // ->withContentType(Http::CONTENT_JSON)
        ;

        // They all have city the same, so this one is 100% safe ;)
        $this->assertEquals('London', $r->param('city'));
        // Grab the individual values unique to each type
        $this->assertEquals('yay', $r->param('getit'));
        $this->assertEquals('cheers', $r->param('postit'));
        $this->assertEquals('rock', $r->param('urlit'));
        $this->assertEquals('awesome', $r->param('jsonit'));
        // Urlparams takes presedence
        $this->assertEquals(35, $r->param('id'));
    }

    public function testParamGetSecond(): void
    {
        $shared = ['city' => 'London', 'id' => 5];
        $r = (new Request())
            ->withGetData(
                ['id' => 15, 'getit' => 'yay'] + $shared)
            ->withPostData(
                ['id' => 25, 'postit' => 'cheers'] + $shared)
            ->withUrlParams(['urlit' => 'rock'])
            ->withBody(json_encode(
                ['id' => 45, 'jsonit' => 'awesome'] + $shared))
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"'])
        ;
        $this->assertEquals(
            ['city' => 'London', 'id' => 15, 'getit' => 'yay'],
            $r->getGetData()
        );
        $this->assertEquals('yay', $r->param('getit'));
        $this->assertEquals(15, $r->param('id'));
    }

    public function testParamPostThird(): void
    {
        $shared = ['city' => 'London', 'id' => 5];
        $r = (new Request())
            ->withGetData(['getit' => 'yay'])
            ->withPostData(
                ['id' => 25, 'postit' => 'cheers'] + $shared)
            ->withUrlParams(['urlit' => 'rock'])
            ->withBody(json_encode(
                ['id' => 45, 'jsonit' => 'awesome'] + $shared))
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"'])
        ;
        $this->assertEquals(
            ['city' => 'London', 'id' => 25, 'postit' => 'cheers'],
            $r->getPostData()
        );
        $this->assertEquals('cheers', $r->param('postit'));
        $this->assertEquals(25, $r->param('id'));
    }


    public function testParamBodyFourth(): void
    {
        $shared = ['city' => 'London', 'id' => 5];
        $json = json_encode(['id' => 45, 'jsonit' => 'awesome'] + $shared);
        $r = (new Request())
            ->withGetData(['getit' => 'yay'])
            ->withPostData(['postit' => 'cheers'])
            ->withUrlParams(['urlit' => 'rock'])
            ->withBody($json)
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"'])
        ;
        $this->assertEquals($json, $r->body());
        $this->assertEquals('awesome', $r->param('jsonit'));
        $this->assertEquals(45, $r->param('id'));
    }

    public function testContent(): void
    {
        $data = ['id' => 45, 'city' => 'London'];
        $r = (new Request())
            ->withPostData($data)
            ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded; Charset="utf-8"'])
        ;
        $this->assertEquals(Http::CONTENT_FORM, $r->contentType());
        $this->assertEquals($data, $r->content());
    }

    public function testParamFromBodyWhenJsonBody(): void
    {
        $r = (new Request())
            ->withBody(json_encode(
                ['id' => 45, 'jsonit' => 'awesome', 'city' => 'London']))
            ->withHeaders(['Content-Type' => 'application/json; Charset="utf-8"'])
        ;
        $this->assertEquals('awesome', $r->param('jsonit'));
        $this->assertEquals(
            ['city' => 'London', 'id' => 45, 'jsonit' => 'awesome'],
            $r->getPostData()
        );
        $this->assertEquals(45, $r->param('id'));
    }
}
