# Remote

Makes http requests using cURL. uses Message for both Request and Response description

### Table of Contents

 - [Class specification](#class-specification)

## Class specification

```php
/**
 * Creatues the Remote instance, only sets configurations
 *
 * @param array $curl_options
 * @param array $config
 */
public function __construct(array $curl_options = [], array $config = [])

/**
 * Make a HTTP GET request to `$url` and return the `Message` response
 *
 * @param string $url
 * @param array $headers
 * @return Message
 */
public function get(string $url, array $headers = []): Message

/**
 * Make a HTTP POST request to `$url`, posting `$data` as a json body, and return the `Message` response
 *
 * @param string $url
 * @param array $data
 * @param array $headers
 * @param string $method Http::POST | Http::PUT
 * @return Message
 */
public function postJson(string $url, array $data, array $headers = [], string $method = Http::POST): Message

/**
 * Make a HTTP POST request to `$url`, posting `$data` as a "form" and return the `Message` response
 *
 * @param string $url
 * @param array $data
 * @param array $headers
 * @param string $method Http::POST | Http::PUT
 * @return Message
 */
public function postForm(string $url, array $data, array $headers = [], string $method = Http::POST): Message

/**
 * Make a HTTP DELETE request to `$url` and return the `Message` response
 *
 * @param string $url
 * @param array $headers
 * @return Message
 */
public function delete(string $url, array $headers = []): Message

/**
 * Make a HTTP request as specified by `$message` and return the `Message` response
 *
 * @param Message $message
 * @return Message
 */
public function http(Message $message): Message
```