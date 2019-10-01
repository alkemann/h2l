# Dispatch

When using H2L as a full on microframework for serving web applications (api or html), the
*Dispatch* class is the entryway and should be the start of all stacks (except possibly
error/exception handling). This means that normally you would use an `index.php` (see
skeleton examples) that includes the Composer autoloader, embeds any configurations (see
Environment and Connections) and then passes all PHP globals into the Dispatch. It will
analyse the request, use the Router to pick a Route and then execute the route callable
(or fallback) and then return a response. That you can then echo.

### Table of Contents

 - [Class specification](#class-specification)
 - [Minium usage example](#minimum-usage)

## Minimum usage

#### Example
```php
$ROOT = realpath(dirname(dirname(__FILE__)));
require_once($ROOT . '/vendor/autoload.php');
alkemann\h2l\Environment::put('content_path', $ROOT . '/content/pages/');
$dispatch = new alkemann\h2l\Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
echo $dispatch->response(); // Returns a Response object, but toString calls render method
```

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