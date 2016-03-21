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

    public function __construct(Request $request)
    {
        $this->_request = $request;
        $this->_type    = $request->type();
        $this->_url     = $request->url();

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

    // @TODO refactor, and cache
    private function head() : string
    {
        ob_start();
        $render = $this;
        $type = $this->_type;

        $headfile = LAYOUT_PATH . $this->layout . DS . 'head.' . $type . '.php';
        if (file_exists($headfile))
            (function($sldkfjlksejflskjflskdjflskdfj) {
                extract($this->_data);
                include $sldkfjlksejflskjflskdjflskdfj;
            })($headfile);

        $neckfile = LAYOUT_PATH . $this->layout . DS . 'neck.' . $type . '.php';
        if (file_exists($neckfile))
            (function($lidsinqjhsdfytqkwjkasjdksadsdg) {
                extract($this->_data);
                include $lidsinqjhsdfytqkwjkasjdksadsdg;
            })($neckfile);

        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    // @TODO refactor, and cache
    private function foot() : string
    {
        $type = $this->_type;

        $footfile = LAYOUT_PATH .  $this->layout . DS . 'foot.' . $type . '.php';
        if (!file_exists($footfile))
            return '';

        ob_start();
        (function($ldkfoskdfosjicyvutwehkshfskjdf) {
            extract($this->_data);
            include $ldkfoskdfosjicyvutwehkshfskjdf;
        })($footfile);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    // @TODO refactor, and cache
    public function view($view) : string
    {
        $file = CONTENT_PATH . 'pages' . DS . $view . '.php';
        if (!file_exists($file)) {
            // throw new \alkemann\hl\core\exceptions\InvalidUrlException($file);
            dd("view file does not exist", $file, $view, $this);
        }
        ob_start();
        (function($dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd) { // or another way to hide the file variable?
            extract($this->_data);
            include $dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd;
        })($file);
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }

    public function render()
    {
        $contentType = $this->contentType();
        header("Content-type: $contentType");
        $response = $this->head();
        $response .= $this->view($this->viewToRender());
        $response .= $this->foot();
        echo $response;
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
        $ret = join(DS, $this->_path) . DS . $this->_view . '.' . $this->_type;
        return trim($ret, DS);
    }
}
