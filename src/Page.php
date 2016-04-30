<?php

namespace alkemann\h2l;

/**
 * Class Page
 *
 * @package alkemann\h2l
 */
class Page implements Response
{
    /**
     * Overwrite this in view templates to set layout, i.e. `$this->layout = 'slim';`
     *
     * @var string
     */
    public $layout = 'default';

    /**
     * @var Request
     */
    protected $_request;
    protected $_data = [];
    protected $_url;
    protected $_template;
    protected $_type = 'html';
    protected $_validTypes = ['html','json', 'xml'];
    protected $_contentTypes = [
        'html' => 'text/html',
        'json' => 'application/json',
        'xml' => 'application/xml'
    ];
    protected $_config = [];

    /**
     * Page constructor.
     *
     * Analyze the request url to convert to a view template
     *
     * @param Request $request
     * @param array $config
     */
    public function __construct(Request $request, array $config = [])
    {
        $this->_request  = $request;
        $this->_config   = $config;
        $this->_type     = $request->type();
        $this->_url      = $request->route()->url;
        $this->_template = $config['template'] ?? $this->templateFromUrl();
    }

    /**
     * Provide data (variables) that are to be extracted into the view (and layout) templates
     *
     * @param string/array $key an array of data or the name for $value
     * @param null $value if $key is a string, this can be the value of that var
     */
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

    /**
     * @return Request
     */
    public function request() : Request
    {
        return $this->_request;
    }

    // @TODO refactor, and cache
    private function head():string
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
    private function foot():string
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
    public function view($view):string
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

    /**
     * Set header type, render the view, then optionally render layouts and wrap the template
     *
     * @TODO injectable header function
     * @return string fully rendered string, ready to be echo'ed
     * @throws exceptions\InvalidUrl if the view template does not exist
     */
    public function render()
    {
        $contentType = $this->contentType();
        header("Content-type: $contentType");
        $view = $this->view($this->_template);
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

    private function templateFromUrl()
    {
        $parts = \explode('/', $this->_url);
        $last = \array_slice($parts, -1, 1, true);
        unset($parts[key($last)]);
        $view = current($last);
        $period = strrpos($view , '.');
        if ($period) {
            $type = substr($view , $period + 1);
            if (in_array($type, $this->_validTypes)) {
                $this->_type = $type;
                $view = substr($view , 0, $period);
            }
        }

        $ret = join(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . $view . '.' . $this->_type;
        return trim($ret, DIRECTORY_SEPARATOR);
    }
}
