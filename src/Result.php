<?php

namespace alkemann\h2l;

class Result implements Response
{

    private $_request;
    private $_content;
    private $_format;
    private $_config;

    private $_validTypes = ['html','json', 'xml'];
    private $_contentTypes = ['html' => 'text/html', 'json' => 'application/json', 'xml' => 'application/xml'];

    public function __construct($content = null, string $format = "json", array $config = [])
    {
        $this->_config = $config;
        $this->_content = $content;
        $this->_format = $format;
    }

    public function render()
    {
        $contentType = $this->contentType($this->_format);
        $h = $this->_config['headerFunc'] ?? 'header';
        $h("Content-type: $contentType");
        $content = $this->setContent($this->_content);
        return $content;
    }

    private function setContent($content)
    {
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

    private function contentType(string $format)
    {
        if (in_array($format, $this->_validTypes)) {
            return $this->_contentTypes[$format];
        }
        return "text/html";
    }
}
