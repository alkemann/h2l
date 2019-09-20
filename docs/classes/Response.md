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

    public function code(): int
    {
        return $this->message->code();
    }

    public function contentType(): string
    {
        return $this->message->contentType();
    }

    public function message(): ?Message
    {
        return $this->message;
    }

    /**
     * @throws \Error if the configured `header_fun` is not callable
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

    abstract public function render(): string;
}
