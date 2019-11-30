<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{
    Environment, exceptions\InvalidUrl, Request, Response, response\Error, util\Http
};

class ErrorTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        Environment::setEnvironment(Environment::TEST);
    }

    public function testConstructAndHeaderInjection(): void
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 406;
        $e = new Error([], compact('header_func', 'code'));
        $this->assertInstanceOf(Response::class, $e);
        $this->assertInstanceOf(Error::class, $e);
        $result = $e->render();
        $this->assertEquals('', $result);
        $expected = ['HTTP/1.1 406 Not Acceptable', 'Content-Type: text/html'];
        $this->assertEquals($expected, $header);
        $this->assertEquals("text/html", $e->contentType());
    }

    public function testConstructWithRequest(): void
    {
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $request = $this->getMockBuilder(Request::class)
            // ->setMockClassName('Request')
            ->disableOriginalConstructor()
            ->setMethods(['acceptType', 'contentType', 'route', 'method']) // mocked methods
            ->getMock();

        $request->expects($this->once())->method('acceptType')->willReturn('application/json');
        $content_path = '/tmp';
        $code = 404;
        $e = new Error([], compact('header_func', 'content_path', 'code', 'request'));
        $this->assertInstanceOf(Error::class, $e);
        $this->assertEquals("application/json", $e->contentType());
    }

    public function test404(): void
    {
        Environment::put('debug', false);
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 404;
        $e = new Error([], compact('header_func', 'code'));
        $e->render();
        $this->assertEquals(404, $e->code());
        $expected = ['HTTP/1.1 404 Not Found', 'Content-Type: text/html'];
        $this->assertEquals($expected, $header);
    }

    public function test404WithErrorPageWithMessage(): void
    {
        Environment::put('debug', false);
        $header = [];
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $code = 404;
        $content_type = 'application/xml';
        $e = new Error(['message' => 'Not Found'], compact('header_func', 'content_type', 'code'));
        $e->render();
        $expected = ['HTTP/1.1 404 Not Found', 'Content-Type: application/xml'];
        $this->assertEquals($expected, $header);
    }

    public function test404WithDebug(): void
    {
        Environment::set([
            'debug' => true,
            'content_path' => '/tmp/',
        ], "test404WithDebug");
        Environment::setEnvironment('test404WithDebug');

        $p = new class() {
            public function setData() {}
            public function render()
            {
                throw new InvalidUrl("NO/PAGE");
            }
            public function isValid()
            {
                return true;
            }
        };
        $header_func = function($h) use (&$header) {$header[] = $h; };
        $page_class = get_class($p);
        $code = 404;
        $e = new Error([], compact('header_func', 'page_class', 'code'));
        $expected = "No error page made at NO/PAGE";
        $result = $e->render();
        $this->assertEquals($expected, $result);

        Environment::setEnvironment(Environment::TEST);
    }

    /**
     * @expectedException Error
     */
    public function testHeaderException(): void
    {
        $e = new Error([], ['header_func' => 99]);
        $e->render();
    }

    public function testErrorFromRequest(): void
    {
        $request = (new Request)
            ->withServerParams(['HTTP_ACCEPT' => 'application/json;q=0.9']);
        $error = new Error([], compact('request'));
        $this->assertSame($request, $error->request());
        $this->assertEquals(Http::CONTENT_JSON, $error->contentType());
    }
}
