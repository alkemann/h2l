# Dispatch


### Table of Contents

 - [Class specification](#class-specification)

## Class specification
```php

/**
 * Analyze request, provided $_REQUEST, $_SERVER [, $_GET, $_POST] to identify Route
 *
 * Response type can be set from HTTP_ACCEPT header
 * Call setRoute or setRouteFromRouter to set a route
 *
 * @param array $request $_REQUEST
 * @param array $server $_SERVER
 * @param array $get $_GET
 * @param array $post $_POST
 * @param null|interfaces\Session $session if null, a default Session with $_SESSION will be created
 */
public function __construct(
    array $request = [],
    array $server = [],
    array $get = [],
    array $post = [],
    interfaces\Session $session = null
)

/**
 * Tries to identify route by configured static, dynamic or fallback configurations.
 *
 * First a string match on url is attempted, then fallback to see if Page is configured
 * with a 'content_path'. Then checks if Router has a fallback callback configured.
 * Return false if all 3 fails to set a route.
 *
 * @param string $router class name to use for Router matching
 * @return bool true if a route is set on the Request
 */
public function setRouteFromRouter(string $router = Router::class): bool

/**
 * @return interfaces\Route|null Route identified for request if set
 */
public function route(): ?interfaces\Route

/**
 * Recreates the `Request` with the specified `Route`. `Request` may be created as side effect.
 *
 * @param interfaces\Route $route
 */
public function setRoute(interfaces\Route $route): void

/**
 * Execute the Route's callback and return the Response object that the callback is required to return
 *
 * Catches InvalidUrl exceptions and returns a response\Error with 404 instead
 *
 * @return Response|null
 * @throws NoRouteSetError if a route has not been set prior to calling this, or by a middleware
 */
public function response(): ?Response

/**
 * Add a closure to wrap the Route callback in to be called during Request::response
 *
 * @param callable|callable[] ...$cbs
 */
public function registerMiddle(callable ...$cbs): void

```