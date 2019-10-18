<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\Environment;
use alkemann\h2l\util\Http;

/**
 * Class Json
 *
 * @package alkemann\h2l
 */
class Json extends Response
{
    /**
     * @param mixed $content JSON encodable payload
     * @param int $code HTTP code to respond with, defaults to `200`
     * @param array $config inject config/overrides like `header_func`
     */
    public function __construct($content = null, int $code = Http::CODE_OK, array $config = [])
    {
        $this->config = $config + [
            'encode_options' => Environment::get('json_encode_options', JSON_THROW_ON_ERROR),
            'encode_depth' => Environment::get('json_encode_depth', 512),
            'container' => Environment::get('json_encode_container', true),
        ];
        $this->message = (new Message())
            ->withCode($code)
            ->withHeaders(['Content-Type' => Http::CONTENT_JSON])
            ->withBody($this->encodeAndContainData($content))
        ;
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

    /**
     * @param mixed $content something that can be Json Enccoded
     * @return string
     * @throws \JsonException on encode errors
     */
    private function encodeAndContainData($content): string
    {
        if (empty($content)) {
            return "";
        }
        if ($content instanceof \Generator) {
            $content = iterator_to_array($content);
        }
        $container = $this->config['container'] ? ['data' => $content] : $content;
        return json_encode(
            $container,
            $this->config['encode_options'],
            $this->config['encode_depth']
        );
    }
}
