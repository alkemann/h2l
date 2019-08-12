# Router

The most essential class to understand when not using the [Automagic routing from files](../concepts/Page.md).
The Router class allows you to set explicitly routes, as well as dynamic (url matching
a regex) and a fall back (404 Not Found essentially).

### Table of Contents

 - [Common usage](#common-usage)
 - [Dynamic routes](#dynamic-routes)
 - [Alias routes](#alias-routes)
 - [Class specification](#class-specification)

## Common usage

The class is used statically and you primarily only need to think about the `Router::add`
method, which you will call once for each route in your application. If `content_path` is
not configured in the `Environment`, then it makes sense to add a fallback route to
catch-all and provide a 404 NOT FOUND.

Along with each route, you provide the callable (closures in the example below). These
callables must return a `Response` (something that can render to string) or null. And
they will be passed the `Request`.

#### Example:

```php
use alkemann\h2l\{Router, Request, Response, util\Http};
use alkemann\h2l\response\{Json, Text, Html };

Router::add(
    '/', // i.e. http://localhost:8088/
    function(Request $request): Response {
        return new Page(
            ['msg' => 'Hello World!'],
            ['template' => 'home']
        );
    }
);

Router::add(
    '/status', // i.e. http://localhost:8088/status
    function(Request $request): Response {
        return new Json(['status' => 'ok']);
    }
);

Router::add(
    'lines', // i.e. http://localhost:8088/lines
    function(Request $request): Response {
        return new Text(['One line', 'Second line']);
    }
}; 

Router::fallback(
    // Catching all other urls than those configure above
    function(Request $request): Response {
        $url = $request->url();
        return new Text("404 ERROR: '$url' not found", 404);
    }
);

```

### Dynamic routes

While these explicit routes are all nice an all, often we want dynamic routes where parts of
the url string changes but we want the same code to handle it. Say for example
`http://localhost:80/api/users/12/profile` and `http://localhost:80/api/users/65/profile`.
These are obviously the same action, but we put the "id" of the user in the url instead of
as a query parameter. Of course we could have done so as well. Quick example (that skips
several other checks, like if the user exists):

#### Example:

```php
Router::add('/api/users/profile', function(Request $request): Json {
    $id = $request->param('id');
    if (!$id) {
        return new Json(
            ['error' => 'Bad request, `id` required'],
            ['code' => Http::CODE_BAD_REQUEST]
        );
    }
    return new Json(['user' => User::get($id)]);
});
```

But this above solution is not as eligant as the one were we put required path parameters
in the url. To do so we use a regex instead of the straight up string comparison. The
`$url` argument to the add method is the normal regex match you would pass to `preg_match`,
and it is important to name the match groups you want to extract. Notice that the pip
character (`|`) use set up as defailt delimiter, you may change this using `Router::$DELIMITER`.

#### Example:

```php
Router::add(
    "|api/users/(?<id>\d+)/profile|",
    function(Request $request): Json {
        $id = $request->param('id');
        return new Json(['user' => User::get($id)]);
    }
);
```
We specify here that we only want to match digits as value parts of the user id (`\d`),
requireing at least one (`+`) and that we wish to store this capture in the match group `id`.
This is then available through the same `Request::param` method as before.

If we have multiple types of objects that has this same "profile" action, we could (not
saying we should), join them into one route with some further magic:

#### Example:

```php
Router::add(
    "|api/(?<model>users|cars)/(?<id>\d+)/profile|",
    function(Request $request): Json {
        $id = $request->param('id');
        switch ($request->param('model')) {
            case 'users':
                $data = ['user' => User::get($id)]; break;
            case 'cars':
                $data = ['car' => Car::get($id)]; break;
        }
        return new Json($data);
    }
);
```

## Alias routes

If the [Automagic routing from files](../concepts/Page.md). is used, you may want to create
aliases, meaning that instead of `http://example.com/home.hml` you want the "home" template
to respond to `http://example.com/`. This is simple to set up with the <em>alias</em> method.

#### Example:

```php
alkemann\h2l\Router::alias('/', 'home.html');
```

## Class specification

```php
// Defines which character is used by regex dynamic routes
public static string $DELIMITER = '|';

/**
 * Add new dynamic route to application
 *
 * @param string $url string or regex match, /w named groups
 * @param callable $callable
 * @param array|string $methods Http::<GET/POST/PUT/PATCH/DELETE>
 */
public static function add(
    string $url,
    callable $callable,
    $methods = [Http::GET]
): void

/**
 * Add an alias route, i.e. `/` as alias for `home.html`
 *
 * @param string $alias
 * @param string $real
 */
public static function alias(string $alias, string $real): void

/**
 * Sets fallback route to be used if no other route is matched
 *
 * @param callable $callable
 */
public static function fallback(callable $callable): void
```
