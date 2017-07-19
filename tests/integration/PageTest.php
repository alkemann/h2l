<?php

namespace alkemann\h2l\tests\integration;

use alkemann\h2l\{Page, Route, Request, Response};

class PageTest extends \PHPUnit_Framework_TestCase
{

    public function testRenderingSimple()
    {
        try {
            $config = $this->_setupFolder();
            $this->_setupViewFiles($config);
        } catch (\Throwable $t) {
            $this->markTestSkipped("Skipping File test: " . $t->getMessage());
        }

        $request = $this->getMockBuilder('alkemann\h2l\Request')
            ->setMockClassName('Request')
            ->disableOriginalConstructor()
            ->setMethods(['type', 'route', 'method']) // mocked methods
            ->getMock();

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
