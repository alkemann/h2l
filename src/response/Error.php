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
    public $type = 'html';
    /**
     * @var array
     */
    protected $_config = [];

    public function __construct(?string $content = null, int $code = 500, array $config = [])
    {

        foreach (['data', 'type', 'message'] as $key) {
            if (isset($config[$key])) {
                $this->{$key} = $config[$key];
                unset($config[$key]);
            }
        }
        $this->code = $code;
        $this->content = $content;
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

        if ($this->content) {
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
        } else {
            $h("Content-type: {$this->contentType()}");
        }
        return '';
    }
}
