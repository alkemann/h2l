<?php

namespace alkemann\h2l;

class Error implements Response
{

    protected $_error;
    protected $_message;

    public function __construct($errorCode, $message = null)
    {
        $this->_error = $errorCode;
        $this->_message = $message;
    }

    public function render()
    {
        switch ($this->_error) {
            case 404:
                header("HTTP/1.0 404 Not Found");
                break;
            case 400:
                header("HTTP/1.0 400 {$this->_message}");
                break;
            default:
                header("HTTP/1.0 {$this->_error} Bad request");
                break;
        }
    }
}
