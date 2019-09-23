# Response


### Table of Contents

 - [Class specification](#class-specification)

## Class specification

```php
abstract class Response;

/**
 * Returns the HTTP Code of the response
 *
 * @return int
 */
public function code(): int

/**
 * Returns the content type of the messe part of the response
 *
 * @return string
 */
public function contentType(): string

/**
 * Returns the `alkemann\Message` object part of the response
 *
 * @return Message
 */
public function message(): ?Message

/**
 * All subclasses of Response must implement render to return the string body
 *
 * @return string
 */
abstract public function render(): string;

```