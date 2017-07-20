<?php

namespace alkemann\h2l\response;

/**
 * Class Json
 *
 * @package alkemann\h2l
 */
class Json implements \alkemann\h2l\Response
{

    private $_code;
    private $_content;
    private $_message;
    private $_config;

    /**
     * Result constructor.
     *
     * @param mixed $content The content in raw format to render, i.e. an object that implements \JsonSerializable
     * @param string $format 'html', 'json' or 'xml'
     * @param array $config Inject
     */
    public function __construct($content = null, int $code = 200, string $message = "", array $config = [])
    {
        $this->_config = $config;
        $this->_content = $content;
        $this->_code = $code;
        $this->_message = $message;
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     *
     * @return string
     */
    public function render() : string
    {
        $h = $this->_config['header_func'] ?? 'header';
        $h("Content-type: application/json");
        if ($this->_code != 200) {
            if ($this->_message)
                $h("HTTP/1.0 {$this->_code} {$this->_message}");
            else
                $h("HTTP/1.0 {$this->_code} Bad request");

        }
        return $this->formattedContent();
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
