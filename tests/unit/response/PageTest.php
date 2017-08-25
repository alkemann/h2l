<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{
    Environment, exceptions\ConfigMissing, response\Page, Router, Route, Request, Response
};

class PageTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        Environment::setEnvironment(Environment::TEST);
    }

    public function testConstruct()
    {
        /**
         * @var $request Request
         */
        $request = (new Request)
            ->withRequestParams(['url' => 'places/norway', 'filter' => 'all'])
            ->withServerParams([
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'REQUEST_URI' => '/places/norway?filter=all',
                'REQUEST_METHOD' => 'GET',
            ])
            ->withGetData(['filter' => 'all'])
            ->withUrl('places/norway')
        ;
        $request = $request->withRoute(Router::match($request->url(), $request->method()));
        $page = Page::fromRequest($request);
        $this->assertTrue($page instanceof Response);
        $this->assertTrue($page instanceof Page);
        $this->assertSame($request, $page->request());

        $ref_templateFromUrl = new \ReflectionMethod($page, 'templateFromUrl');
        $ref_templateFromUrl->setAccessible(true);

        $this->assertEquals('text/html', $page->contentType());
        $this->assertEquals('places' . DIRECTORY_SEPARATOR . 'norway.html', $ref_templateFromUrl->invoke($page, 'places/norway'));
    }

    public function testJsonFormat()
    {
        /**
         * @var $request Request
          */
        $request = (new Request)
            ->withRequestParams(['url' => 'tasks'])
            ->withServerParams([
                'HTTP_ACCEPT' => 'application/json;q=0.8',
                'REQUEST_URI' => '/tasks',
                'REQUEST_METHOD' => 'GET',
            ])
            ->withUrl('tasks')
        ;
        $request = $request->withRoute(Router::match($request->url(), $request->method()));
        $page = Page::fromRequest($request);
        $this->assertEquals('application/json', $page->contentType());
    }

    public function testJsonUrlpart()
    {
        /**
         * @var $request Request
          */
        $request = (new Request)
            ->withRequestParams(['url' => 'tasks.json'])
            ->withServerParams([
                'HTTP_ACCEPT' => '*/*;q=0.8',
                'REQUEST_URI' => '/tasks.json',
                'REQUEST_METHOD' => 'GET',
            ])
            ->withUrl('tasks.json')
        ;
        $request = $request->withRoute(Router::match($request->url(), $request->method()));
        $page = Page::fromRequest($request);
        $this->assertEquals('application/json', $page->contentType());
    }

    public function testBadFormatUsesHtml()
    {
        $conf = [];
        /**
         * @var $request Request
         */
        $request = (new Request)
            ->withRequestParams(['url' => 'somethig.csv'])
            ->withServerParams([
                'HTTP_ACCEPT' => '*/*;q=0.8',
                'REQUEST_URI' => '/somethig.csv',
                'REQUEST_METHOD' => 'GET'
            ])
            ->withUrl('somethig.csv')
        ;
        $request = $request->withRoute(Router::match($request->url(), $request->method()));
        $page = Page::fromRequest($request, $conf);
        $this->assertEquals('text/html', $page->contentType());
    }

    public function testSetData()
    {
        $request = new Request;
        $request = $request->withRoute(Router::match($request->url(), $request->method()));
        $page = Page::fromRequest($request);
        $page->setData('place', 'Norway');
        $page->setData(['city' => 'Oslo', 'height' => 12]);

        $ref_data = new \ReflectionProperty($page, 'data');
        $ref_data->setAccessible(true);
        $expected = ['place' => 'Norway', 'city' => 'Oslo', 'height' => 12];
        $result = $ref_data->getvalue($page);
        $this->assertEquals($expected, $result);
    }

    public function testMissingContentPathConfigs()
    {
        $this->expectException(ConfigMissing::class);
        Environment::setEnvironment('testMissingContentPathConfigs');
        $r = (new Request)->withUrl('testMissingContentPathConfigs');
        $r = $r->withRoute(Router::match($r->url()));
        $h = function() {};
        $page = Page::fromRequest($r, ['header_func' => $h]);
        $page->render();
        Environment::setEnvironment(Environment::TEST);
    }

    public function testMissingLayoutConfigs()
    {
        $conf = Environment::grab(Environment::TEST);
        Environment::setEnvironment('testMissingContentPathConfigs');
        unset($conf['layout_path']);
        Environment::set($conf, 'testMissingContentPathConfigs');
        $r = (new Request)->withUrl('place');
        $r = $r->withRoute(Router::match($r->url()));
        $h = function() {};
        $page = Page::fromRequest($r, ['header_func' => $h]);
        $expected = "<h1>Win!</h1>";
        $result = $page->render();
        $this->assertEquals($expected, $result);
    }
}
