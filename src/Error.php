<?php

namespace alkemann\h2l;

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
     * @var array
     */
    protected $_config;

    /**
     * Error constructor.
     *
     * @param int $errorCode
     * @param string|null $message
     * @param array $config
     */
    public function __construct(int $errorCode, string $message = null, array $config = [])
    {
        $this->code = $errorCode;
        $this->message = $message;
        $this->_config = $config + [
            'page_class' => Page::class,
            'request_class' => Request::class
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
        $errorPage = null;
        $page_class = $this->_config['page_class'];
        $request_class = $this->_config['request_class'];
        switch ($this->code) {
            case 404:
                $h("HTTP/1.0 404 Not Found");
                $errorPage = new $page_class(new $request_class(['url' => '404']), $this->_config);
                break;
            case 400:
                $h("HTTP/1.0 400 {$this->message}");
                break;
            default:
                $h("HTTP/1.0 {$this->code} Bad request");
                $errorPage = new $page_class(new $request_class(['url' => '500']), $this->_config);
                break;
        }
        if ($errorPage) {
            // TODO only do this if request accepts html?
            try {
                $errorPage->setData('message', $this->message);
                return $errorPage->render();
            } catch (\alkemann\h2l\exceptions\InvalidUrl $e) {
                if (defined('DEBUG') && DEBUG) {
                    return "No error page made at " . $e->getMessage();
                }
            }
        }
        return '';
    }
}
