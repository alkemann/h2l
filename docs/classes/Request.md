# Request

Request extends [`alkemann\h2l\Message`](Message.md) and is the container of the HTTP request,
describing everything everything known about the request to the server. It's brother
[Response](Response.md) likewise extends Message and is the container of everything we want
to send in the response. They are most commonly used as the input and output of any routed
handler. A route handler will have expected to have the format `function(Request $request): Response`.
See [Router examples](Router.md) for details.

### Table of Contents

 - [Class specification](#class-specification)

## Class specification
```php
class Request extends Message
```
### Methods
```php
/**
 * Get request parameters from url as url params, get queries or post, in that order
 *
 * @param string $name the name of the parameter
 * @return mixed|null the value or null if not set
 */
public function param(string $name)

/**
 * Returns the full url (with domain) of the request, alternatively for the provided path
 *
 * Alternative usage is for creating full urls, i.e. reverse routing
 *
 * @param null|string $path
 * @return string
 */
public function fullUrl(?string $path = null): string

/**
 * Recreate the `Request` with specified request parameters
 *
 * @param array $request_params
 * @return Request
 */
public function withRequestParams(array $request_params): Request

/**
 * Returns the request parameters of the request
 *
 * @return array
 */
public function getRequestParams(): array

/**
 * Recreates the `Request` with specified server parameters
 *
 * @param array $server
 * @return Request
 */
public function withServerParams(array $server): Request

/**
 * Returns the header specified accept type(s) of the request
 *
 * @return string
 */
public function acceptType(): string

/**
 * Returns the server parameters of the request
 *
 * @return array
 */
public function getServerParams(): array

/**
 * Returns the server parameter value of `$name` or null if not set
 *
 * @param string $name
 * @return null|string
 */
public function getServerParam(string $name): ?string

/**
 * Recreates the `Request` with the specified post data
 *
 * @param array $post
 * @return Request
 */
public function withPostData(array $post): Request

/**
 * Returns the post data of the request
 *
 * @return array
 */
public function getPostData(): array

/**
 * Recreates the `Request` with the specified get (quary) data
 *
 * @param array $get
 * @return Request
 */
public function withGetData(array $get): Request

/**
 * Returns the request data of the request
 *
 * @return array
 */
public function getGetData(): array

/**
 * Alias of `getGetData`
 *
 * @return array
 */
public function query(): array

/**
 * Recreates the `Request` with the specified Url parameters
 *
 * Url parameters are extracted with dynamic routes, aka:
 * `/api/location/(?<city>\w+)/visit` the "city" part.
 *
 * @param array $parameters
 * @return Request
 */
public function withUrlParams(array $parameters): Request

/**
 * Returns the url parameters of the request
 *
 * @return array
 */
public function getUrlParams(): array

/**
 * Recreates the `Request` with the specified Route
 *
 * @param interfaces\Route $route
 * @return Request
 */
public function withRoute(interfaces\Route $route): Request

/**
 * Returns the `Route` of the request, if set, null if not.
 *
 * @return interfaces\Route|null
 */
public function route(): ?interfaces\Route

/**
 * Recreates the `Request` with the given `Session` object
 *
 * @param interfaces\Session $session
 * @return Request
 */
public function withSession(interfaces\Session $session): Request

/**
 * Returns the page variables (those variables to be injected into templates) of request
 *
 * @return array
 */
public function pageVars(): array

/**
 * Recreates the `Request` with the given page variables
 */
public function withPageVars(array $vars): Request

/**
 * Returns the session var at $key or the Session object
 *
 * First call to this method will initiate the session
 *
 * @param string $key Dot notation for deeper values, i.e. `user.email`
 * @return mixed|interfaces\Session
 */
public function session(?string $key = null)

/**
 * Redirect NOW the request to $url
 *
 * Method includes usage of the `exit` php command
 *
 * @codeCoverageIgnore
 * @param $url
 */
public function redirect($url)

```

### Inherited Methods from Message
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
