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
        $ref_templateFromUrl = new \ReflectionMethod($page, 'templateFromUrl');
        $ref_templateFromUrl->setAccessible(true);

        $this->assertEquals('text/html', $ref_contentType->invoke($page));
        $this->assertEquals('places' . DIRECTORY_SEPARATOR . 'norway.html', $ref_templateFromUrl->invoke($page));
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

    public function testRenderingSimple()
    {
        try {
            $config = $this->_setupFolder();
            $this->_setupViewFiles($config);
        } catch (\Throwable $t) {
            //
            $this->markTestSkipped("Skipping File test: " . $t->getMessage());
        }

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
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $page->layout = 'spicy';
        $expected = '<html><title>Spice</title><body><h1>Win!</h1></body></html>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $page->layout = 'doesntexist';
        $expected = '<h1>Win!</h1>';
        $result = $page->render();
        $this->assertEquals($expected, $result);

        $this->_cleanupViewFiles();

        $caught = false; // invalid url;
        try {
            $result = $page->render();
        } catch (\alkemann\h2l\exceptions\InvalidUrl $e) {
            $caught = true;
        }
        $this->assertTrue($caught, 'Exception was not thrown for missing page');

    }

    private function _setupFolder()
    {
        $path = sys_get_temp_dir();
        $h2l = $path . DIRECTORY_SEPARATOR . 'h2l';
        if (file_exists($h2l))
            $this->recursiveDelete($h2l);
        $cpath = $path . DIRECTORY_SEPARATOR . 'h2l' . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . 'pages';
        mkdir($cpath, 777, true);
        $lpath = $path . DIRECTORY_SEPARATOR . 'h2l' . DIRECTORY_SEPARATOR . 'layouts';
        mkdir($lpath, 777, true);
        mkdir($lpath . DIRECTORY_SEPARATOR . 'default', 777, true);
        mkdir($lpath . DIRECTORY_SEPARATOR . 'spicy', 777, true);
        return ['content_path' => $cpath . DIRECTORY_SEPARATOR, 'layout_path' => $lpath . DIRECTORY_SEPARATOR];
    }

    private function _setupViewFiles(array $config)
    {
        // view file
        $f = $config['content_path'] . DIRECTORY_SEPARATOR . 'place.html.php';
        file_put_contents($f, '<h1>Win!</h1>');
        $f = $config['content_path'] . DIRECTORY_SEPARATOR . 'task.json.php';
        file_put_contents($f, '{"id": 12, "title": "Win"}');
        // layout one folder with head, neck and foot
        $lp = $config['layout_path'] . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;
        file_put_contents($lp.'head.html.php', '<html><body>');
        file_put_contents($lp.'neck.html.php', '<div>');
        file_put_contents($lp.'foot.html.php', '</div></body></html>');
        // layout two folder with head, and foot
        $lp = $config['layout_path'] . DIRECTORY_SEPARATOR . 'spicy' . DIRECTORY_SEPARATOR;
        file_put_contents($lp.'head.html.php', '<html><title>Spice</title><body>');
        file_put_contents($lp.'foot.html.php', '</body></html>');
    }

    private function _cleanupViewFiles()
    {
        $path = sys_get_temp_dir();
        $h2l = $path . DIRECTORY_SEPARATOR . 'h2l';
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
