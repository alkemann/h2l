<?php

namespace alkemann\h2l;

/**
 * Class Message
 *
 * Container for requests and responses made with the Remote class
 *
 * @package alkemann\h2l
 */
final class Message
{
    const CONTENT_JSON = 'application/json';
    const CONTENT_FORM = 'application/x-www-form-urlencoded';
    const CONTENT_HTML = 'text/html';
    const CONTENT_TEXT = 'text/plain';
    const CONTENT_XML = 'text/xml';

    const REQUEST = "REQUEST";
    const RESPONSE = "RESPONSE";
    /**
     * @var string  "REQUEST"|"RESPONSE"
     */
    private $type;

    /**
     * @var int
     */
    private $code;
    /**
     * @var string
     */
    private $url = '';
    /**
     * Enum with Request::GET, Request::POST etc
     * @var string
     */
    private $method = Request::GET;
    /**
     * @var string
     */
    private $body;
    /**
     * @var array
     */
    private $meta = [];
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var array
     */
    private $options = [];
    /**
     * @var string
     */
    private $content_type = 'text/html';
    /**
     * @var string
     */
    private $content_charset = 'utf-8';

    /**
     * @return null|string
     */
    public function type(): ?string
    {
        return $this->type;
    }

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
            case static::CONTENT_JSON:
                return json_decode($this->body, true);
            case static::CONTENT_XML:
                return new \SimpleXMLElement($this->body);
            case static::CONTENT_HTML:
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
     * @param string $type
     * @return Message
     */
    public function withType(string $type): Message
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
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
        $content_header = $new->header('Content-Type');
        if (is_string($content_header)) {
            if (strpos($content_header, ';') === false) {
                $new->content_type = trim(strtolower($content_header));
            } else {
                list($type, $other) = explode(';', $content_header, 2);
                $new->content_type = trim(strtolower($type));
                list($key, $charset) = explode('=', $other, 2);
                if ('charset' === strtolower(trim($key))) {
                    $new->content_charset = strtolower(trim(trim($charset, '"')));
                }
            }
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
