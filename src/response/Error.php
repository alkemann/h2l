<?php

namespace alkemann\h2l\response;

use alkemann\h2l\{Request, Response, response\Page, Log};

/**
 * Class Error
 *
 * @package alkemann\h2l
 */
class Error extends Response
{

    protected $code = 500;
    protected $type = 'html';
    protected $data = [];
    protected $request = null;

    protected $_config = [];

    public function __construct(array $config = [])
    {

        foreach (['data', 'type', 'code', 'request'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }

        if ($this->request) {
            $this->type = $this->request->type();
        } else {
            // @TODO dependency injection?
            $httaccept = $_SERVER['HTTP_ACCEPT'] ?? '*/*';
            if ($httaccept !== '*/*' && strpos($httaccept, 'application/json') !== false) {
                $this->type = 'json';
            }
        }

        $this->_config = $config + [
            'page_class' => Page::class
        ];
    }

    /**
     * @throws \Error
     */
    public function render() : string
    {
        $h = $this->_config['header_func'] ?? 'header';
        if (is_callable($h) == false) {
            throw new \Error("Header function injected to Error is not callable");
        }
        $page_class = $this->_config['page_class'];

        $msg = self::$code_to_message[$this->code];
        $h("HTTP/1.0 {$this->code} {$msg}");

        try {
            $page_config = $this->_config + [
                'code' => $this->code,
                'template' => $this->code == 404 ? '404' : 'error',
                'type' => $this->type,
                'data' => $this->data
            ];
            $page = new $page_class($page_config);
            return $page->render();
        } catch (\alkemann\h2l\exceptions\InvalidUrl $e) {
            Log::debug("No error page made at " . $e->getMessage());
            if (defined('DEBUG') && DEBUG) {
                return "No error page made at " . $e->getMessage();
            }
        }
        return '';
    }
}
