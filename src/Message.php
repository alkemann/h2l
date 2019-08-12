<?php

namespace alkemann\h2l;

use alkemann\h2l\util\Http;

/**
 * Class Message
 *
 * Container for requests and responses made with the Remote class
 *
 * @package alkemann\h2l
 */
class Message
{

    /**
     * @var int
     */
    protected $code;
    /**
     * @var string
     */
    protected $url = '';
    /**
     * Enum with Http::GET, Http::POST etc
     * @var string
     */
    protected $method = Http::GET;
    /**
     * @var string
     */
    protected $body;
    /**
     * @var array
     */
    protected $meta = [];
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var string
     */
    protected $content_type = Http::CONTENT_HTML;
    /**
     * @var string
     */
    protected $content_charset = 'utf-8';

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return null|string
     */
    public function body(): ?string
    {
        return $this->body;
    }

    /**
     * @return null|string|array|\SimpleXMLElement body converted from raw format
     */
    public function content()
    {
        switch ($this->contentType()) {
            case Http::CONTENT_JSON:
                return json_decode($this->body, true);
            case Http::CONTENT_XML:
                return new \SimpleXMLElement($this->body);
            case Http::CONTENT_HTML:
                $doc = new \DOMDocument();
                $doc->loadHTML($this->body);
                return $doc;
            case null:
            default:
                return $this->body;
        }
    }

    /**
     * @return string
     */
    public function contentType(): string
    {
        return $this->content_type;
    }

    /**
     * @return string
     */
    public function charset(): string
    {
        return $this->content_charset;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function header(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return $value;
            }
        }
        return null;
    }

    /**
     * @param string $class name of class that must take data array as constructor
     * @return object body json decoded and sent to constructor of $class
     */
    public function as(string $class)
    {
        return new $class($this->content());
    }

    /**
     * @return array
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return int|null
     */
    public function code(): ?int
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return Message
     */
    public function withCode(int $code): Message
    {
        $new = clone $this;
        $new->code = $code;
        return $new;
    }

    /**
     * @param string $url
     * @return Message
     */
    public function withUrl(string $url): Message
    {
        $new = clone $this;
        $new->url = $url;
        return $new;
    }

    /**
     * @param string $method
     * @return Message
     */
    public function withMethod(string $method): Message
    {
        $new = clone $this;
        $new->method = $method;
        return $new;
    }

    /**
     * @param string $body
     * @return Message
     */
    public function withBody(string $body): Message
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * @param array $headers
     * @return Message
     */
    public function withHeaders(array $headers): Message
    {
        $new = clone $this;
        $new->headers = $headers;
        $new->setContentHeaderTypeAndCharset();
        return $new;
    }

    private function setContentHeaderTypeAndCharset(): void
    {
        $content_header = $this->header('Content-Type');
        if (is_string($content_header)) {
            if (strpos($content_header, ';') === false) {
                $this->content_type = trim(strtolower($content_header));
            } else {
                list($type, $other) = explode(';', $content_header, 2);
                $this->content_type = trim(strtolower($type));
                list($key, $charset) = explode('=', $other, 2);
                if ('charset' === strtolower(trim($key))) {
                    $this->content_charset = strtolower(trim(trim($charset, '"')));
                }
            }
        }
    }

    public function withHeader(string $name, string $value): Message
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        if ($name === 'Content-Type') {
            $new->setContentHeaderTypeAndCharset();
        }
        return $new;
    }

    /**
     * @param array $options
     * @return Message
     */
    public function withOptions(array $options): Message
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    /**
     * @param array $meta
     * @return Message
     */
    public function withMeta(array $meta): Message
    {
        $new = clone $this;
        $new->meta = $meta;
        return $new;
    }

    /**
     * @return string the raw body of the message
     */
    public function __toString(): string
    {
        return $this->body ?? '';
    }
}
