<?php

namespace alkemann\h2l\response;

use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\exceptions\ConfigMissing;
use alkemann\h2l\Log;
use alkemann\h2l\Message;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\Environment;
use alkemann\h2l\util\Http;

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
    /**
     * @var array
     */
    protected $data = [];
    /**
     * @var string
     */
    private $template = 'error.html';
    /**
     * @var string
     */
    private $content_type = Http::CONTENT_HTML;
    /**
     * @var int
     */
    private $code = 200;
    /**
     * @var array
     */
    protected $config = [];

    public function __construct($data = [], array $config = [])
    {
        $this->data = $data;
        foreach (['request', 'content_type', 'code', 'layout'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }
        if (isset($config['template'])) {
            $this->setTemplate($config['template']);
        }
        $this->config = $config;

        $this->message = (new Message())
            ->withCode($this->code)
            ->withHeader('Content-Type', $this->content_type)
        ;
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
            'content_type' => $request->acceptType(),
        ];
        $page = new static($request->pageVars(), $config);
        $route = $request->route();
        $url = $route ? $route->url() : $request->url();
        $page->template = $config['template'] ?? $page->templateFromUrl($url);
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
        $headfile = $this->getLayoutFile('head');
        $neckfile = $this->getLayoutFile('neck');
        if (!$headfile && !$neckfile) {
            return '';
        }
        ob_start();
        try {
            if ($headfile && file_exists($headfile)) {
                (function ($sldkfjlksejflskjflskdjflskdfj) {
                    extract($this->data);
                    include $sldkfjlksejflskjflskdjflskdfj;
                })($headfile);
            }

            if ($neckfile && file_exists($neckfile)) {
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

    private function getLayoutFile(string $name): ?string
    {
        $path = $this->config['layout_path'] ?? Environment::get('layout_path');
        if (is_null($path)) {
            return null;
        }
        $ending = Http::fileEndingFromType($this->content_type);
        return $path . $this->layout . DIRECTORY_SEPARATOR . $name . '.' . $ending . '.php';
    }

    private function getContentFile(string $view): string
    {
        $path = $this->config['content_path']
            ?? $this->config['template_path']
            ?? Environment::get('content_path')
            ?? Environment::get('template_path');

        if (is_null($path)) {
            throw new ConfigMissing("No `content_path` or `template_path` configured!");
        }
        return $path . $view . '.php';
    }

    // @TODO refactor, and cache
    private function foot(): string
    {
        $footfile = $this->getLayoutFile('foot');
        if ($footfile && file_exists($footfile)) {
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
        } else {
            return '';
        }
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
        $this->setHeaders();
        $view = $this->view($this->template);
        $response = $this->head();
        $response .= $view;
        $response .= $this->foot();
        $this->message = $this->message->withBody($response);
        return $this->message->body();
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $ending = Http::fileEndingFromType($this->content_type);
        $this->template = "{$template}.{$ending}";
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
            $ending = substr($view, $period + 1);
            if ($type = Http::contentTypeFromFileEnding($ending)) {
                $this->setContentType($type);
                $view = substr($view, 0, $period);
            }
        }
        $ending = Http::fileEndingFromType($this->content_type);
        $ret = join(DIRECTORY_SEPARATOR, $parts) . DIRECTORY_SEPARATOR . $view . '.' . $ending;
        return trim($ret, DIRECTORY_SEPARATOR);
    }

    private function setContentType($type)
    {
        $this->content_type = $type;
        $this->message = $this->message->withHeader('Content-Type', $this->content_type);
    }
}
