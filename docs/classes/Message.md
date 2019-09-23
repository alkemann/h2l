# Message


### Table of Contents

 - [Class specification](#class-specification)

## Class specification

```php
/**
 * @return string
 */
public function url(): string

/**
 * @return string
 */
public function method(): string

/**
 * @return null|string
 */
public function body(): ?string

/**
 * @return null|string|array|\SimpleXMLElement body converted from raw format
 */
public function content()

/**
 * @return string
 */
public function contentType(): string

/**
 * @return string
 */
public function charset(): string

/**
 * @param string $name
 * @return null|string
 */
public function header(string $name): ?string

/**
 * @param string $class name of class that must take data array as constructor
 * @return object body json decoded and sent to constructor of $class
 */
public function as(string $class): object

/**
 * @return array
 */
public function headers(): array

/**
 * @return array
 */
public function meta(): array

/**
 * @return array
 */
public function options(): array

/**
 * @return int|null
 */
public function code(): ?int

/**
 * @param int $code
 * @return Message
 */
public function withCode(int $code): Message

/**
 * @param string $url
 * @return Message
 */
public function withUrl(string $url): Message

/**
 * @param string $method
 * @return Message
 */
public function withMethod(string $method): Message

/**
 * @param string $body
 * @return Message
 */
public function withBody(string $body): Message

/**
 * @param array $headers
 * @return Message
 */
public function withHeaders(array $headers): Message

/**
 * @param string $name
 * @param string $value
 * @return Message
 */
public function withHeader(string $name, string $value): Message

/**
 * @param array $options
 * @return Message
 */
public function withOptions(array $options): Message

/**
 * @param array $meta
 * @return Message
 */
public function withMeta(array $meta): Message

/**
 * @return string the raw body of the message
 */
public function __toString(): string

```