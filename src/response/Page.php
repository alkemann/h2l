<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Environment;
use alkemann\h2l\exceptions\ConfigMissing;
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\Log;
use alkemann\h2l\Message;
use alkemann\h2l\Request;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;

/**
 * Class Page
 *
 * @package alkemann\h2l
 */
final class Page extends Response
{
    /**
     * Overwrite this in view templates to set layout, i.e. `$this->layout = 'slim';`
     *
     * @var string
     */
    public $layout = 'default';
    /**
     * @var null|Request
     */
    protected $request = null;
    /**
     * @var array<string, mixed>
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
     * Constructor
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $config
     */
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
     * @param array<string, mixed> $config
     * @return Page
     */
    public static function fromRequest(Request $request, array $config = []): Page
    {
        $config += [
            'request' => $request,
            'content_type' => $request->acceptType(),
        ];
        $page = new Page($request->pageVars(), $config);
        $route = $request->route();
        $url = $route ? $route->url() : $request->url();
        $page->template = $config['template'] ?? $page->templateFromUrl($url);
        return $page;
    }

    /**
     * Returns true if a file can be found that matches the "template" that is set
     *
     * @return bool
     * @throws InvalidUrl is thrown if the file is missing
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
     * @param string|array<string, mixed> $key an array of data or the name for $value
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

    /**
     * Returns the `Request` of this response
     *
     * @return null|Request
     */
    public function request(): ?Request
    {
        return $this->request;
    }

    /**
     * @TODO refactor, and cache
     * @return string
     */
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
                $sldkfjlksejflskjflskdjflskdfj = $headfile;
                (function() use ($sldkfjlksejflskjflskdjflskdfj) {
                    extract($this->data);
                    include $sldkfjlksejflskjflskdjflskdfj;
                })();
            }

            if ($neckfile && file_exists($neckfile)) {
                $lidsinqjhsdfytqkwjkasjdksadsdg = $neckfile;
                (function() use ($lidsinqjhsdfytqkwjkasjdksadsdg) {
                    extract($this->data);
                    include $lidsinqjhsdfytqkwjkasjdksadsdg;
                })();
            }
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return is_string($ret) ? $ret : '';
    }

    /**
     * @param string $name
     * @return string|null
     */
    private function getLayoutFile(string $name): ?string
    {
        $path = $this->config['layout_path'] ?? Environment::get('layout_path');
        if (is_null($path)) {
            return null;
        }
        $ending = Http::fileEndingFromType($this->content_type);
        return $path . $this->layout . DIRECTORY_SEPARATOR . $name . '.' . $ending . '.php';
    }

    /**
     * @param string $view
     * @return string
     * @throws ConfigMissing if neither content nor template paths are found
     */
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

    /**
     * @TODO refactor, and cache
     * @return string
     */
    private function foot(): string
    {
        $footfile = $this->getLayoutFile('foot');
        if ($footfile && file_exists($footfile)) {
            ob_start();
            try {
                $ldkfoskdfosjicyvutwehkshfskjdf = $footfile;
                (function() use ($ldkfoskdfosjicyvutwehkshfskjdf) {
                    extract($this->data);
                    include $ldkfoskdfosjicyvutwehkshfskjdf;
                })();
            } finally {
                $ret = ob_get_contents();
                ob_end_clean();
            }
            return is_string($ret) ? $ret : '';
        } else {
            return '';
        }
    }

    /**
     * @TODO refactor, and cache
     * @TODO BUG, method should be private
     * @param string $view
     * @return string
     * @throws InvalidUrl if no view file found
     */
    public function view(string $view): string
    {
        $file = $this->getContentFile($view);
        ob_start();
        try {
            // or another way to hide the file variable?
            $dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd = $file;
            (function() use ($dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd) {
                extract($this->data);
                include $dsfjskdfjsdlkfjsdkfjsdkfjsdlkfjsd;
            })();
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return is_string($ret) ? $ret : '';
    }

    /**
     * Render a parts file to include in view for reusing parts
     *
     * @param string $name name of part file to include, doesnt include path or file ending
     * @return string
     * @throws ConfigMissing if `parts_path` is not configured
     */
    public function part(string $name): string
    {
        $parts_path = Environment::get('parts_path');
        if (!$parts_path) {
            throw new ConfigMissing("Missing `parts_path` configuration!");
        }
        $ending = Http::fileEndingFromType($this->content_type);

        $parts_file = $parts_path . "{$name}.{$ending}.php";

        ob_start();
        try {
            $popsemdsdfosjicyvsoaowkdawd = $parts_file;
            (function() use ($popsemdsdfosjicyvsoaowkdawd) {
                extract($this->data);
                include $popsemdsdfosjicyvsoaowkdawd;
            })();
        } finally {
            $ret = ob_get_contents();
            ob_end_clean();
        }
        return is_string($ret) ? $ret : '';
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
     * Creates a filename from template and content type
     *
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $ending = Http::fileEndingFromType($this->content_type);
        $this->template = "{$template}.{$ending}";
    }

    /**
     * @param string $url
     * @return string
     */
    private function templateFromUrl(string $url): string
    {
        $parts = explode('/', $url);
        $last = array_slice($parts, -1, 1, true);
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

    /**
     * @param string $type
     */
    private function setContentType(string $type): void
    {
        $this->content_type = $type;
        $this->message = $this->message->withHeader('Content-Type', $this->content_type);
    }
}
