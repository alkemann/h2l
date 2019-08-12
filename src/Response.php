<?php

namespace alkemann\h2l;

use alkemann\h2l\util\Http;

/**
 * Abstract class Response
 *
 * @package alkemann\h2l
 */
abstract class Response
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var Message
     */
    protected $message;

    /**
     * Returns the HTTP Code of the response
     *
     * @return int
     */
    public function code(): int
    {
        return $this->message->code();
    }

    /**
     * Returns the content type of the messe part of the response
     *
     * @return string
     */
    public function contentType(): string
    {
        return $this->message->contentType();
    }

    /**
     * Returns the `alkemann\Message` object part of the response
     *
     * @return Message
     */
    public function message(): ?Message
    {
        return $this->message;
    }

    /**
     * @throws \Error if the configured `header_func` is not callable
     */
    protected function setHeaders(): void
    {
        $h = $this->config['header_func'] ?? 'header';
        if (is_callable($h) === false) {
            throw new \Error("header_func is not callable");
        }
        $code = $this->message->code();
        if ($code != Http::CODE_OK) {
            $msg = Http::httpCodeToMessage($code);
            $h("HTTP/1.1 {$code} {$msg}");
        }

        foreach ($this->message->headers() as $name => $value) {
            $h("{$name}: $value");
        }
    }

    /**
     * All subclasses of Response must implement render to return the string body
     *
     * @return string
     */
    abstract public function render(): string;
}
