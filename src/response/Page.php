<?php

namespace alkemann\h2l\response;

use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\exceptions\ConfigMissing;
use alkemann\h2l\Log;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\Environment;

/**
 * Class Page
 *
 * @package alkemann\h2l
 */
class Page extends Response
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
    protected $request;
    protected $data = [];
    protected $template = 'error';

    protected $config = [];

    public function __construct($data = [], array $config = [])
    {
        $this->data = $data;
        foreach (['request', 'type', 'code'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
            }
        }
        if (isset($config['template'])) {
            $this->setTemplate($config['template']);
        }
        $this->config = $config;
    }

    /**
     * Analyze the request url to convert to a view template
     *
     * @param Request $request
     * @param array $config
     * @return Page
     */
    public static function fromRequest(Request $request, array $config = []): Page
    {
        $config += [
            'request' => $request,
            'type' => $request->type(),
        ];
        $page = new static([], $config);
        $page->template = $config['template'] ?? $page->templateFromUrl($request->route()->url());
        return $page;
    }

    /**
     * @return bool
     * @throws InvalidUrl
     */
    public function isValid(): bool
    {
        $file = $this->getContentFile($this->template);
        if (!file_exists($file)) {
            throw new InvalidUrl("Missing view file: [ $file ]");
        }
        return true;
    }

    /**
     * Provide data (variables) that are to be extracted into the view (and layout) templates
     *
     * @param string|array $key an array of data or the name for $value
     * @param mixed $value if $key is a string, this can be the value of that var
     */
    public function setData($key, $value = null): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->data[$k] = $v;
            }
        } else {
            $this->data[$key] = $value;
        }
    }

    public function request(): ?Request
    {
        return $this->request;
    }

    // @TODO refactor, and cache
    private function head(): string
    {
        ob_start();
        try {
            $headfile = $this->getLayoutFile('head');
            if (file_exists($headfile)) {
                (function ($sldkfjlksejflskjflskdjflskdfj) {
                    extract($this->data);
                    include $sldkfjlksejflskjflskdjflskdfj;
                })($headfile);
            }

            $neckfile = $this->getLayoutFile('neck');
            if (file_exists($neckfile)) {
                (function ($lidsinqjhsdfytqkwjkasjdksadsdg) {
                    extract($this->data);
                    include $lidsinqjhsdfytqkwjkasjdksadsdg;
                })($neckfile);
            }
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    private function getLayoutFile(string $name): string
    {
        $path = $this->config['layout_path'] ?? Environment::get('layout_path');
        if (is_null($path)) {
            Log::debug("As there is no configured `layout_path` in Environment, layouts are skipped");
        }
        return $path . $this->layout . DIRECTORY_SEPARATOR . $name . '.' . $this->type . '.php';
    }

    private function getContentFile(string $view): string
    {
        $path = $this->config['content_path'] ?? Environment::get('content_path');
        if (is_null($path)) {
            throw new ConfigMissing("Page requires a `content_path` in Environment");
        }
        return $path . $view . '.php';
    }

    // @TODO refactor, and cache
    private function foot(): string
    {
        $footfile = $this->getLayoutFile('foot');
        if (!file_exists($footfile)) {
            return '';
        }

        ob_start();
        try {
            (function ($ldkfoskdfosjicyvutwehkshfskjdf) {
                extract($this->data);
                include $ldkfoskdfosjicyvutwehkshfskjdf;
            })($footfile);
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    // @TODO refactor, and cache

    /**
     * @param string $view
     * @return string
     * @throws InvalidUrl
     */
    public function view(string $view): string
    {
        $file = $this->getContentFile($view);
        ob_start();
        try {
            // or another way to hide the file variable?
            (function ($dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd) {
                extract($this->data);
                include $dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd;
            })($file);
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return $ret;
    }

    /**
     * Set header type, render the view, then optionally render layouts and wrap the template
     *
     * @return string fully rendered string, ready to be echo'ed
     * @throws InvalidUrl if the view template does not exist
     */
    public function render(): string
    {
        $h = $this->config['header_func'] ?? 'header';
        $contentType = $this->contentType();
        $h("Content-type: $contentType");
        $view = $this->view($this->template);
        $response = $this->head();
        $response .= $view;
        $response .= $this->foot();
        return $response;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = "{$template}.{$this->type}";
    }

    /**
     * @param null|string $url
     * @return string
     */
    private function templateFromUrl(?string $url = null): string
    {
        $parts = \explode('/', $url);
        $last = \array_slice($parts, -1, 1, true);
        unset($parts[key($last)]);
        $view = current($last);
        $period = strrpos($view, '.');
        if ($period) {
            $type = substr($view, $period + 1);
            if (in_array($type, $this->validTypes)) {
                $this->type = $type;
                $view = substr($view, 0, $period);
            }
        }

        $ret = join(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . $view . '.' . $this->type;
        return trim($ret, DIRECTORY_SEPARATOR);
    }
}
