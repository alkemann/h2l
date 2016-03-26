<?php

namespace alkemann\h2l;

class Error implements Response
{

    public $code;
    public $message;
    protected $_config;

    public function __construct(int $errorCode, string $message = null, array $config = [])
    {
        $this->code = $errorCode;
        $this->message = $message;
        $this->_config = $config;
    }

    public function render()
    {
        $h = $this->_config['header_func'] ?? 'header';
        if (is_callable($h) == false) {
            throw new \Error("Header function injected to Error is not callable");
        }
        switch ($this->code) {
            case 404:
                $h("HTTP/1.0 404 Not Found");
                break;
            case 400:
                $h("HTTP/1.0 400 {$this->message}");
                break;
            default:
                $h("HTTP/1.0 {$this->code} Bad request");
                break;
        }
    }
}
