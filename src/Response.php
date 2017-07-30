<?php

namespace alkemann\h2l;

/**
 * Interface Response
 *
 * @package alkemann\h2l
 */
abstract class Response
{

    public static $code_to_message = [
         // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
         // Successful 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
         // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
         // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
         // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
    ];

    protected $type = 'html';

    protected $_validTypes = ['html', 'json', 'xml'];
    protected $_contentTypes = [
        'html' => 'text/html',
        'json' => 'application/json',
        'xml' => 'application/xml'
    ];

    public function type(): string
    {
        return $this->type;
    }

    protected function contentType(): string
    {
        $format = $this->type;
        if (in_array($format, $this->_validTypes)) {
            return $this->_contentTypes[$format];
        }
        return "text/html";
    }

    abstract public function render(): string;
}
