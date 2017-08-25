<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Environment;
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\Log;
use alkemann\h2l\Request;
use alkemann\h2l\Response;

/**
 * Class Error
 *
 * @package alkemann\h2l
 */
class Error extends Response
{
    protected $content_type = 'text/html';
    protected $code = 500;
    protected $data = [];
    /**
     * @var Request
     */
    protected $request = null;
    protected $config = [];

    public function __construct(array $data = [], array $config = [])
    {
        $this->data = $data;
        foreach (['content_type', 'code', 'request'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }

        if ($this->request) {
            $this->content_type = $this->request->acceptType();
        }

        $this->config = $config + [
                'page_class' => Page::class
            ];
    }

    /**
     * @throws \Error
     */
    public function render(): string
    {
        $h = $this->config['header_func'] ?? 'header';
        if (is_callable($h) === false) {
            throw new \Error("Header function injected to Error is not callable");
        }
        $page_class = $this->config['page_class'];

        $msg = self::$code_to_message[$this->code];
        $h("HTTP/1.0 {$this->code} {$msg}");
        try {
            $page_config = $this->config + [
                    'code' => $this->code,
                    'template' => $this->code == 404 ? '404' : 'error',
                    'content_type' => $this->content_type,
                    'request' => $this->request
                ];
            $data = $this->data + ['code' => $this->code];
            /**
             * @var $page Page
             */
            $page = new $page_class($data, $page_config);
            $page->isValid();

            return $page->render();
        } catch (InvalidUrl $e) {
            Log::debug("No error page made at " . $e->getMessage());
            if (Environment::get('debug')) {
                return "No error page made at " . $e->getMessage();
            }
        }
        return '';
    }
}
