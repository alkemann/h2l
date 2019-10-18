# Concept : Dispatch cycle

The Dispatch class is responsible for creating the Request object that describes
the current request. You may then ask the Router to identify the correct route
that fits this Request.

The constructor of the Dispatch is where all dependency injection of how that
Request will be described happens.

Once the Dispatch has a Request with a Route, it is possible to ask it to
produce a Response. At this point, Middleware are permitted to modify
(albeit in an immutable fashion) the Request or return their own Response
before at end of the chain of Middlewares, the callback in the Route is
called.

Consider the following minimal implementation of an `index.php`. We pass in
to the constructor of Dispatch all the request describing globals, after
this point we will no longer interact with them directly. We then ask the
Dispatcher to use the default Router to analyze the request and set a
Route. We get the Response (if there is any) and then we ask it render itself.

```php
$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
/** @var alkemann\h2l\Response */
$response = $dispatch->response();
if ($response instanceof Response) {
    echo $response->render();
}
```

You can override the Router behavior in several ways, first, you may set the
Route directly (preseumably having used a different Router):

```php
$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRoute($route_that_is_manually_defined);
$response = $dispatch->response();
echo $response; // Since Response's toString calls render
```

Of course a better way to use a custom Router is to implement the Router
(`alkemann\h2l\interfaces\Router`) interface and pass that to Dispatch:

```php
$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter(\app\CustomRouter::class);
$response = $dispatch->response();
echo $response;
```

#### Further concept reading

- [Route](route.md)
- [Request](request.md)
- [Response](response.md)

#### Relevant class docs

- [Dispatch](../classes/displatch.md)
- [Route](../classes/route.md)
- [Router](../classes/router.md)
- [Request](../classes/request.md)
- [Response](../classes/response.md)
