<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Message;

/**
 * Class Json
 *
 * @package alkemann\h2l
 */
class Json extends \alkemann\h2l\Response
{
    protected $content_type = 'json';

    private $content;
    private $config;

    public function __construct($content = null, int $code = 200, array $config = [])
    {
        $this->config = $config;
        $this->content = $content;
        $this->code = $code;
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     */
    public function render(): string
    {
        $this->setHeaders();
        return $this->formattedContent();
    }

    private function setHeaders(): void
    {
        $h = $this->config['header_func'] ?? 'header';
        $h("Content-type: application/json");
        if ($this->code != 200) {
            $msg = Message::httpCodeToMessage($this->code);
            $h("HTTP/1.0 {$this->code} {$msg}");
        }
    }

    private function formattedContent(): string
    {
        $content = $this->content;
        if (empty($content)) {
            return "";
        }
        if ($content instanceof \Generator) {
            $content = iterator_to_array($content);
        }
        return json_encode($content);
    }
}
