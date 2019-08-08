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
     * @param string $content Content to render
     * @param int $code HTTP code to respond with, defaults to `200`
     * @param array $config inject config/overrides like `header_func`
     */
    public function __construct(string $content, int $code = Http::CODE_OK, array $config = [])
    {
        $this->config = $config;
        $this->message = (new Message())
            ->withCode($code)
            ->withHeaders([
                'Content-Type' => Http::CONTENT_TEXT
            ])
            ->withBody($content)
        ;
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     */
    public function render(): string
    {
        $this->setHeaders();
        return $this->message->body();
    }
}