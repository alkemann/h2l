<?php

namespace alkemann\h2l;

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
        $page = new Page($request);
        $this->assertTrue($page instanceof Response);
        $this->assertTrue($page instanceof Page);
        $this->assertSame($request, $page->request());

        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $ref_viewToRender = new \ReflectionMethod($page, 'viewToRender');
        $ref_viewToRender->setAccessible(true);

        $this->assertEquals('text/html', $ref_contentType->invoke($page));
        $this->assertEquals('places' . DS . 'norway.html', $ref_viewToRender->invoke($page));
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
        $page = new Page($request);
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
        $page = new Page($request);
        $ref_contentType = new \ReflectionMethod($page, 'contentType');
        $ref_contentType->setAccessible(true);
        $this->assertEquals('application/json', $ref_contentType->invoke($page));
    }

    public function testSetData()
    {
        $page = new Page(new Request);
        $page->setData('place', 'Norway');
        $page->setData(['city' => 'Oslo', 'height' => 12]);

        $ref_data = new \ReflectionProperty($page, '_data');
        $ref_data->setAccessible(true);
        $expected = ['place' => 'Norway', 'city' => 'Oslo', 'height' => 12];
        $result = $ref_data->getvalue($page);
        $this->assertEquals($expected, $result);
    }

    // @TODO test rendering view with data extracted
    // @TODO test rendering different layouts
    // @TODO test rendering head and foot and embedding view

    public function testRenderingSimple()
    {
        $config = $this->_setupFolder();
        $this->_setupViewFiles($config);

        // $request = new Request(); // @TODO use a mock instead
        $request = $this->getMock(
            'alkemann\h2l\Request',
            ['type', 'route', 'method'], // mocked methods
            [], // no constructor arguments
            'Request',
            false
        );
        $request->expects($this->once())->method('type')->willReturn('html');
        $request->expects($this->once())->method('route')->willReturn(
            new Route('place')
        );

        $config['content_path'] = substr($config['content_path'], 0, -6);
        $page = new Page($request, $config);

        $expected = '<html><body><div><h1>Win!</h1></div></body></html>';
        $result = $page->render(false);        
        $this->assertEquals($expected, $result);

        $page->layout = 'spicy';
        $expected = '<html><title>Spice</title><body><h1>Win!</h1></body></html>';
        $result = $page->render(false);        
        $this->assertEquals($expected, $result);

        $this->_cleanupViewFiles();
    }

    private function _setupFolder()
    {
        $path = sys_get_temp_dir();
        $h2l = $path . DS . 'h2l';
        if (file_exists($h2l))
            $this->recursiveDelete($h2l);
        $cpath = $path . DS . 'h2l' . DS . 'content' . DS . 'pages';
        mkdir($cpath, 777, true);
        $lpath = $path . DS . 'h2l' . DS . 'layouts';
        mkdir($lpath, 777, true);
        mkdir($lpath . DS . 'default', 777, true);
        mkdir($lpath . DS . 'spicy', 777, true);
        return ['content_path' => $cpath . DS, 'layout_path' => $lpath . DS];
    }

    private function _setupViewFiles(array $config)
    {
        // view file
        $f = $config['content_path'] . DS . 'place.html.php';
        file_put_contents($f, '<h1>Win!</h1>');
        $f = $config['content_path'] . DS . 'task.json.php';
        file_put_contents($f, '{"id": 12, "title": "Win"}');
        // layout one folder with head, neck and foot
        $lp = $config['layout_path'] . DS . 'default' . DS;
        file_put_contents($lp.'head.html.php', '<html><body>');
        file_put_contents($lp.'neck.html.php', '<div>');
        file_put_contents($lp.'foot.html.php', '</div></body></html>');
        // layout two folder with head, and foot
        $lp = $config['layout_path'] . DS . 'spicy' . DS;
        file_put_contents($lp.'head.html.php', '<html><title>Spice</title><body>');
        file_put_contents($lp.'foot.html.php', '</body></html>');
    }

    private function _cleanupViewFiles()
    {
        $path = sys_get_temp_dir();
        $h2l = $path . DS . 'h2l';
        if (file_exists($h2l))
            $this->recursiveDelete($h2l);
    }

    /**
     * Delete a file or recursively delete a directory
     *
     * @param string $str Path to file or directory
     */
    private function recursiveDelete($str) {
        if (is_file($str)) {
            return @unlink($str);
        }
        elseif (is_dir($str)) {
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path) {
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }
}
