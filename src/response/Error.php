<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Log;
use alkemann\h2l\Response;

/**
 * Class Error
 *
 * @package alkemann\h2l
 */
class Error extends Response
{

    protected $code = 500;
    protected $data = [];
    protected $request = null;

    protected $config = [];

    public function __construct(array $data = [], array $config = [])
    {
        $this->data = $data;
        foreach (['type', 'code', 'request'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }

        if ($this->request) {
            $this->type = $this->request->type();
        } else {
            $this->grabTypeFromGlobals();
        }

        $this->config = $config + [
                'page_class' => Page::class
            ];
    }

    /**
     * @TODO dependency injection?
     * @codeCoverageIgnore
     */
    private function grabTypeFromGlobals()
    {
        $httaccept = $_SERVER['HTTP_ACCEPT'] ?? '*/*';
        if ($httaccept !== '*/*' && strpos($httaccept, 'application/json') !== false) {
            $this->type = 'json';
        }
    }

    /**
     * @throws \Error
     */
    public function render(): string
    {
        $h = $this->config['header_func'] ?? 'header';
        if (is_callable($h) == false) {
            throw new \Error("Header function injected to Error is not callable");
        }
        $page_class = $this->config['page_class'];

        $msg = self::$code_to_message[$this->code];
        $h("HTTP/1.0 {$this->code} {$msg}");

        try {
            $page_config = $this->config + [
                    'code' => $this->code,
                    'template' => $this->code == 404 ? '404' : 'error',
                    'type' => $this->type
                ];
            $data = $this->data + ['code' => $this->code];
            $page = new $page_class($data, $page_config);
            return $page->render();
        } catch (\alkemann\h2l\exceptions\InvalidUrl $e) {
            Log::debug("No error page made at " . $e->getMessage());
            if (Environment::current() === Environment::DEV) {
                return "No error page made at " . $e->getMessage();
            }
        }
        return '';
    }
}
