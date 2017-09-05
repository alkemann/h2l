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
Dispatcher to analyze the request and set a Route. We get the Response
(if there is any) and then we ask it render itself.

```php
$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
$response = $dispatch->response();
if ($response) {
    echo $response->render();
}
```

#### Further reading

- [Route](route.md)
- [Request](request.md)
- [Response](response.md)
