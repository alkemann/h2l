# Middleware

Middlewares are ways of adding to the `Request` or the `Response`, or in fact
completely replace them, and doing so without changing code of the intended
Routed action. It is a good system for adding logging or security checks or
tidying up the response. Anything that should be applied to many actions but
is not part of handling the specifics of the Routed action.

## How do they work?
Middlewares are callables with the following interface:
```php
function(Request $request, Chain $chain): Response;
```
They recieve the `Request` (either the original, or a different one created by
a middleware before them in the chain) and the `Chain` with contains the stack
of all the middlewares and the `Route` at the end. It knows what to call next,
you must do to create the `Response`, and you do that like this:
```php
$response = $chain->next($request, $chain);
```
The exception to this is ofc if your middleware has decided to "escape hatch"
the dispatch and create it's own `Response` to return instead. This possibility
is one of the reasons why the order is important to keep in mind.

In the end of all middlewares they must return the created `Response`.

Keep in mind that both `Request` and `Response` are immutable and also both
extend `Message`.

So an "empty" middleware closure that you start from looks like this:
```php
use alkemann\h2l\{ Environment, Request, Response, util\Chain };

$log_middle = function(Request $request, Chain $chain): Response {

    // Insert BEFORE activity here

    /** @var Response $response */
    $response = $chain->next($request, $chain);

    // Insert AFTER activity here

    return $response;
};
```

## Activating Middlewares
The `Dispatch` is responsible for running the process that converts a
`Request` into a `Response`. First you set the Route (normally using the
`Router` in `dispatch->setRouteFromRouter()` and then you ask it for the
`Response`. It will create a stack off all middlewares and adding the Routed
action at the end. This allows all middleware to act before the "action",
and also to reacto the response as it is passed back up the chain.

#### To add Middlewares to the Dispatch
```php
$my_middleware = function(Request $r, Chain $c): Response { /* ... */ };
$dispatch->registerMiddle($my_middleware);
```
You can also register an array of all your middlewares at once as the argument
for `registerMiddle` is `callable ...$callables`:
```php
$middlewares = [
    function(Request $r, Chain $c): Response { /* ... */ };
    function(Request $r, Chain $c): Response { /* ... */ };
];
$dispatch->registerMiddle($middlewares);
```

### Using Environment
You can (and probably should) have a config file with all your middlewares,
this allows them to be hosted together and ensures that the order makes the
most sense (order matters). Since they are called in the order they are added.

This way you can also use a different set of middlewares depending on the
active enviroment (only log on dev/local for example).

#### Example config/middelwares.php
```php
use alkemann\h2l\{ Environment, Request, Response, util\Chain };

$log_middle = function(Request $r, Chain $c): Response { /* .. */ };
Environment::addMiddle($log_middle, Environment::DEV);
Environment::addMiddle($log_middle, Environment::LOCAL);

Environment::addMiddle(function(Request $request, Chain $chain): Response {
    /* .. */
}, Environment::ALL);
```

<span style="color:red;">NOTE: This only sets up the potential middlewares,
it DOES NOT ACTIVATE them. You have to do the next step for that.</span>

#### This is then activated in `index.php`:
```php
// After `Environment::setCurrent` has ensured the right one is active
// Before `$response = $dispatch->response();` is called
$dispatch->registerMiddle(...Environment::middlewares());
```

## Example: Log Request/Response
Here you can see a middleware that demonstrates the ability for "before" and
"after" actions in to log the request as it came in and the response going out.
```php
use alkemann\h2l\{ Environment, Request, Response, util\Chain, Log };

// Log request and response
$log_middle = function(Request $request, Chain $chain): Response {
    Log::info("== REQUEST == URL[" . $r->url() . "] ==");
    /** @var Response $response */
    $response = $chain->next($request, $chain);
    $class = get_class($response);
    $code = $response->code();
    Log::info("== RESPONSE == TYPE[{$class}] == CODE[{$code}] ==");
    return $response;
};
```

You can also check out the [Tidy Example](../examples/tidy.md) for another
example on replacing the Response.
