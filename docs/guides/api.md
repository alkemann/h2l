# H2L - microframework

Welcome to H2L a micro framework for PHP 7.1+

## Guide : API

You can easily add API routes through the file and folder structure of pages, or you can use the router to add closures
that respond to the matched urls.

### For example:

```php
<?php

use alkemann\h2l\{Request, Router, response\Json};
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
```

If you wish to structure your controller code a little more, you can use the new feature of the PHP Closure class, like so:

```php
<?php

namespace app;

use alkemann\h2l\{Request, Response, Router};
use alkemann\h2l\response\Json;
use app\Task;
use Closure;

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
            Router::add($url, Closure::fromCallable([$this, $func]), $method);
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