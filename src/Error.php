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
        $errorPage = null;
        switch ($this->code) {
            case 404:
                $h("HTTP/1.0 404 Not Found");
                $errorPage = new Page(new Request(['url' => '404']), $this->_config);
                break;
            case 400:
                $h("HTTP/1.0 400 {$this->message}");
                break;
            default:
                $h("HTTP/1.0 {$this->code} Bad request");
                $errorPage = new Page(new Request(['url' => '500']), $this->_config);
                break;
        }
        if ($errorPage) {
            // TODO only do this if request accepts html?
            try {
                $errorPage->setData('message', $this->message);
                echo $errorPage->render();
            } catch (\alkemann\h2l\exceptions\InvalidUrl $e) {
                if (defined('DEBUG') && DEBUG) {
                    echo "No error page made at " . $e->getMessage();
                }
            }
        }
    }
}
