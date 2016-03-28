<?php

namespace alkemann\h2l;

class Page implements Response
{
    public $layout = 'default';

    protected $_data = [];
    protected $_request;
    protected $_url;
    protected $_path;
    protected $_view;
    protected $_type = 'html';
    protected $_validTypes = ['html','json', 'xml'];
    protected $_contentTypes = [
        'html' => 'text/html',
        'json' => 'application/json',
        'xml' => 'application/xml'
    ];
    protected $_config = [];

    public function __construct(Request $request, array $config = [])
    {
        $this->_request = $request;
        $this->_config  = $config;
        $this->_type    = $request->type();
        $this->_url     = $request->route()->url;

        // @TODO find a different way to do this
        $parts = \explode('/', $this->_url);
        $last = \array_slice($parts, -1, 1, true);
        unset($parts[key($last)]);
        $this->_path = $parts;
        $this->_view = current($last);
        $period = strrpos($this->_view , '.');
        if ($period) {
            $type = substr($this->_view , $period + 1);
            if (in_array($type, $this->_validTypes)) {
                $this->_type = $type;
                $this->_view = substr($this->_view , 0, $period);
            }
        }
    }

    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->_data[$k] = $v;
            }
        } else {
            $this->_data[$key] = $value;
        }
    }

    public function request() : Request
    {
        return $this->_request;
    }

    // @TODO refactor, and cache
    private function head() : string
    {
        ob_start();
        try {
            $headfile = $this->getLayoutFile('head');
            if (file_exists($headfile))
                (function($sldkfjlksejflskjflskdjflskdfj) {
                    extract($this->_data);
                    include $sldkfjlksejflskjflskdjflskdfj;
                })($headfile);

            $neckfile = $this->getLayoutFile('neck');
            if (file_exists($neckfile))
                (function($lidsinqjhsdfytqkwjkasjdksadsdg) {
                    extract($this->_data);
                    include $lidsinqjhsdfytqkwjkasjdksadsdg;
                })($neckfile);

        } catch (\Throwable $t) {
            throw $t;
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    private function getLayoutFile(string $name)
    {
        $path = $this->_config['layout_path'] ?? LAYOUT_PATH;
        return $path . $this->layout . DIRECTORY_SEPARATOR . $name . '.' . $this->_type . '.php';
    }

    // @TODO refactor, and cache
    private function foot() : string
    {
        $footfile = $this->getLayoutFile('foot');
        if (!file_exists($footfile))
            return '';

        ob_start();
        try {
            (function($ldkfoskdfosjicyvutwehkshfskjdf) {
                extract($this->_data);
                include $ldkfoskdfosjicyvutwehkshfskjdf;
            })($footfile);
        } catch (\Throwable $t) {
            throw $t;
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    // @TODO refactor, and cache
    public function view($view) : string
    {
        $file = $this->getContentFile($view);
        if (!file_exists($file)) {
            throw new \alkemann\h2l\exceptions\InvalidUrl($file);
        }
        ob_start();
        try {
            (function($dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd) { // or another way to hide the file variable?
                extract($this->_data);
                include $dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd;
            })($file);
        } catch (\Throwable $t) {
            throw $t;
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    private function getContentFile($view)
    {
        $path = $this->_config['content_path'] ?? CONTENT_PATH;
        return $path . 'pages' . DIRECTORY_SEPARATOR . $view . '.php';
    }

    public function render()
    {
        $contentType = $this->contentType();
        header("Content-type: $contentType");
        $view = $this->view($this->viewToRender());
        $response = $this->head();
        $response .= $view;
        $response .= $this->foot();
        return $response;
    }

    private function contentType()
    {
        $format = $this->_type;
        if (in_array($format, $this->_validTypes)) {
            return $this->_contentTypes[$format];
        }
        return "text/html";
    }

    private function viewToRender()
    {
        $ret = join(DIRECTORY_SEPARATOR, $this->_path) . DIRECTORY_SEPARATOR . $this->_view . '.' . $this->_type;
        return trim($ret, DIRECTORY_SEPARATOR);
    }
}
