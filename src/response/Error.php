<?php

namespace alkemann\h2l\response;

use alkemann\h2l\{Request, Response, response\Page, Log};

/**
 * Class Error
 *
 * @package alkemann\h2l
 */
class Error implements Response
{
    /**
     * @var int
     */
    public $code;
    /**
     * @var string
     */
    public $message;
    /**
     * @var string
     */
    public $content;
    /**
     * @var string
     */
    public $type;
    /**
     * @var array
     */
    protected $_config = [];

    private static $code_to_message = [
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

    public function __construct(?string $content = null, int $code = 500, array $config = [])
    {

        foreach (['data', 'type', 'message'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }
        $this->code = $code;
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

        $message = $this->message ?? self::$code_to_message[$this->code];
        $h("HTTP/1.0 {$this->code} {$message}");

        // TODO only do this if request accepts html?
        try {
            $page_config = $this->_config + ['template' => $this->code == 404 ? '404' : 'error'];
            $page = new $page_class($this->content, $this->code, $page_config);
            if ($this->message) {
                $page->setData('message', $this->message);
            }
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
