<?php declare(strict_types=1);

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
    protected int $code = 0;
    /**
     * @var string
     */
    protected string $url = '';
    /**
     * Enum with Http::GET, Http::POST etc
     * @var string
     */
    protected string $method = Http::GET;
    /**
     * @var string
     */
    protected string $body = '';
    /**
     * @var array<string, mixed>
     */
    protected array $meta = [];
    /**
     * @var array<string, mixed>
     */
    protected array $headers = [];
    /**
     * @var array<string, mixed>
     */
    protected array $options = [];
    /**
     * @var string
     */
    protected string $content_type = Http::CONTENT_HTML;
    /**
     * @var string
     */
    protected string $content_charset = 'utf-8';

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
     * @return string
     */
    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return null|string|array<mixed>|\SimpleXMLElement|\DOMDocument body converted from raw format
     */
    public function content()
    {
        switch ($this->contentType()) {
            case Http::CONTENT_JSON:
                return json_decode($this->body, true, 512, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
            case Http::CONTENT_XML:
                return new \SimpleXMLElement($this->body);
            case Http::CONTENT_HTML:
                $doc = new \DOMDocument();
                $doc->loadHTML($this->body);
                return $doc;
            case Http::CONTENT_FORM:
                // @TODO some php method that converts form to array?
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
    public function as(string $class): object
    {
        return new $class($this->content());
    }

    /**
     * @return array<string, mixed>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        return $this->meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }

    /**
     * @return int
     */
    public function code(): int
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
     * @return static
     */
    public function withBody(string $body): object
    {
        $new = clone $this;
        $new->body = $body;
        return $new;
    }

    /**
     * @param array<string, mixed> $headers
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

    /**
     * @param string $name
     * @param string $value
     * @return Message
     */
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
     * @param array<string, mixed> $options
     * @return Message
     */
    public function withOptions(array $options): Message
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    /**
     * @param array<string, mixed> $meta
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
        return $this->body;
    }
}
