<?php

namespace alkemann\h2l\response;

/**
 * Class Json
 *
 * @package alkemann\h2l
 */
class Json extends \alkemann\h2l\Response
{
    protected $type = 'json';

    private $_code;
    private $_content;
    private $_config;

    public function __construct($content = null, int $code = 200, array $config = [])
    {
        $this->_config = $config;
        $this->_content = $content;
        $this->_code = $code;
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     */
    public function render() : string
    {
        $this->setHeaders();
        return $this->formattedContent();
    }

    private function setHeaders() : void
    {
        $h = $this->_config['header_func'] ?? 'header';
        $h("Content-type: application/json");
        if ($this->_code != 200) {
            $msg = static::$code_to_message[$this->_code];
            $h("HTTP/1.0 {$this->_code} {$msg}");
        }
    }

    private function formattedContent() : string
    {
        $content = $this->_content;
        if (empty($content)) return "";
        if ($content instanceof \Generator) {
            $content = iterator_to_array($content);
        }
        return json_encode($content);
    }
}
