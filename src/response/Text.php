<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;

/**
 * Class Text
 *
 * @package alkemann\h2l
 */
class Text extends Response
{
    /**
     * @param mixed $content Content to render, arrays will be joined by newline, everything else cast to string
     * @param int $code HTTP code to respond with, defaults to `200`
     * @param array $config inject config/overrides like `header_func`
     */
    public function __construct($content, int $code = Http::CODE_OK, array $config = [])
    {
        switch (true) {
            case is_string($content):
                // passthrough
                break;
            case is_array($content) || $content instanceof \Generator:
                $content = trim(static::implode_recur("\n", $content), "\n");
                break;
            default:
                $content = (string) $content;
                break;
        }
        $this->config = $config;
        $this->message = (new Message())
            ->withCode($code)
            ->withHeaders(['Content-Type' => Http::CONTENT_TEXT])
            ->withBody($content)
        ;
    }

    protected static function implode_recur($glue, $arr)
    {
        $output = '';
        foreach ($arr as $v) {
            if (is_array($v)) {
                $output .= static::implode_recur($glue, $v);
            } else {
                $output .= $glue . $v;
            }
        }
        return $output;
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     *
     * @return string
     */
    public function render(): string
    {
        $this->setHeaders();
        return $this->message->body();
    }
}
