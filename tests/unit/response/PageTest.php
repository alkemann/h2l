<?php

namespace alkemann\h2l\tests\unit\response;

use alkemann\h2l\{response\Page, Route, Request, Response};

class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $request = new Request(
            [ // $_REQUEST
                'url' => 'places/norway',
                'filter' => 'all'
            ],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'text/html,*/*;q=0.8',
                'REQUEST_URI' => '/places/norway?filter=all',
                'REQUEST_METHOD' => 'GET',
            ],
            [ // GET
                'url' => 'places/norway',
                'filter' => 'all'
            ]);
        $page = Page::fromRequest($request);
        $this->assertTrue($page instanceof Response);
        $this->assertTrue($page instanceof Page);
        $this->assertSame($request, $page->request());

        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $ref_templateFromUrl = new \ReflectionMethod($page, 'templateFromUrl');
        $ref_templateFromUrl->setAccessible(true);

        $this->assertEquals('text/html', $ref_contentType->invoke($page, 'places/norway'));
        $this->assertEquals('places' . DIRECTORY_SEPARATOR . 'norway.html', $ref_templateFromUrl->invoke($page, 'places/norway'));
    }

    public function testJsonFormat()
    {
        $request = new Request(
            ['url' => 'tasks'],
            [ // $_SERVER
                'HTTP_ACCEPT' => 'application/json;q=0.8',
                'REQUEST_URI' => '/tasks',
                'REQUEST_METHOD' => 'GET',
            ],
            ['url' => 'tasks']);
        $page = Page::fromRequest($request);
        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $this->assertEquals('application/json', $ref_contentType->invoke($page));
    }

    public function testJsonUrlpart()
    {
        $request = new Request(
            ['url' => 'tasks.json'],
            [ // $_SERVER
                'HTTP_ACCEPT' => '*/*;q=0.8',
                'REQUEST_URI' => '/tasks.json',
                'REQUEST_METHOD' => 'GET',
            ],
            ['url' => 'tasks.json']);
        $page = Page::fromRequest($request);
        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $this->assertEquals('application/json', $ref_contentType->invoke($page));
    }

    public function testBadFormatUsesHtml()
    {
        $conf = [];
        $request = new Request(
            ['url' => 'somethig.csv'],
            [
                'HTTP_ACCEPT' => '*/*;q=0.8',
                'REQUEST_URI' => '/tasks.json',
                'REQUEST_METHOD' => 'GET'
            ]
        );
        $page = Page::fromRequest($request, $conf);
        $ref_type = new \ReflectionProperty($page, 'type');
        $ref_type->setAccessible(true);
        $ref_type->setValue($page, 'csv');

        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $this->assertEquals('text/html', $ref_contentType->invoke($page));
    }

    public function testSetData()
    {
        $page = Page::fromRequest(new Request);
        $page->setData('place', 'Norway');
        $page->setData(['city' => 'Oslo', 'height' => 12]);

        $ref_data = new \ReflectionProperty($page, 'data');
        $ref_data->setAccessible(true);
        $expected = ['place' => 'Norway', 'city' => 'Oslo', 'height' => 12];
        $result = $ref_data->getvalue($page);
        $this->assertEquals($expected, $result);
    }

}
