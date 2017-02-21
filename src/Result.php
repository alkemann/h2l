<?php

namespace alkemann\h2l;

/**
 * Class Result
 *
 * @package alkemann\h2l
 */
class Result implements Response
{

    private $_content;
    private $_format;
    private $_config;
    private $_validTypes = ['html','json', 'xml'];
    private $_contentTypes = ['html' => 'text/html', 'json' => 'application/json', 'xml' => 'application/xml'];

    /**
     * Result constructor.
     *
     * @param mixed $content The content in raw format to render, i.e. an object that implements \JsonSerializable
     * @param string $format 'html', 'json' or 'xml'
     * @param array $config Inject
     */
    public function __construct($content = null, string $format = "json", array $config = [])
    {
        $this->_config = $config;
        $this->_content = $content;
        $this->_format = $format;
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
        $contentType = $this->contentType($this->_format);
        $h = $this->_config['header_func'] ?? 'header';
        $h("Content-type: $contentType");
        return $this->formattedContent($this->_content);
    }

    private function formattedContent($content) : string
    {
        if ($content instanceof \Generator) {
            $content = iterator_to_array($content);
        }
        switch ($this->_format) {
            case 'json':
                return json_encode($content);
                break;
            // TODO XML
            default:
                return $content;
                break;
        }
    }

    private function contentType(string $format) : string
    {
        if (in_array($format, $this->_validTypes)) {
            return $this->_contentTypes[$format];
        }
        return "text/html";
    }
}
