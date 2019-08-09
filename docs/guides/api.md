# Guide : API

You can easily add API routes through the file and folder structure of pages, or you can use the router to add closures
that respond to the matched urls.

### For example:

```php
<?php

use alkemann\h2l\{Request, Router, response\Json, response\Text};
use app\Task; // Your model class  that maybe uses the Model and Entity traits

// Get task by id, i.e. http://example.com/api/tasks/12
Router::add('|^/api/tasks/(?<id>\d+)$|', function(Request $request) {
    $id = $request->param('id'); // from the regex matched url part
    $data_model = Task::get($id);
    return new Json($data_model); // since Task model implements \JsonSerializable
}, Request::GET);

// Any url with `version` in it, i.e. http://example.com/somethi/versionista
Router::add('/version', function($r) {
    return new Json(['version' => '1.3']);
});

// A Text version renders strings or newline-joins arrays
Router::add('/names', function($r) {
    return new Text(['Ole', 'Dole', 'Doffen']);
});

// When not using the "Page" automagic error handling, the fallback route catches all
Router::fallback(function(Request $r) {
    return new Json(['error' => 'No such route'], 404);
});
```

If you wish to structure your controller code a little more, we can create closures from callables so:

```php
use alkemann\h2l\{Router, Request, response\Json };
class Api {
    public static function version(Request $r) { return new Json(['version' => '1.0']); }
}
Router::add('/version', 'Api::version');
```

Or a slightly more complex version that doesnt use static methods

```php
<?php

namespace app;

use alkemann\h2l\{Request, Response, Router};
use alkemann\h2l\response\Json;
use app\Task;

class Api
{
    static $routes = [
        // Url                          function    request method
        [
            // Url match
            '|^/api/tasks/(?<id>\d+)$|',

            // method to call in this class to handle the request
            'getTask'

            // HTTP Method(s) to accept for this route
            Request::GET
        ],
        [
            '/version',
            'get_version',
            REQUEST::GET
        ]
    ];

    public function addRoutes(): void
    {
        foreach (static::$routes as [$url, $func, $method]) {
            // [$this, $func] will be converted to closure with Closure::fromCallable
            Router::add($url, [$this, $func], $method);
        }
    }

    public function get_task(Request $request)
    {
        $id = $request->param('id'); // from the regex matched url part
        $data_model = Task::get($id);
        return new Json($data_model); // since Task model implements \JsonSerializable
    }

    public function get_version(Request $request) {
        return new Json(['version' => '1.3']);
    }
}
```