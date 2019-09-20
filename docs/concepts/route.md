# Concept : Route

A **Route** is a rule or ruleset that helps the application figure out what action
to run or page to return.

It can be a strict string match, a regex with named group match or a path that
matches a page view in the `/content/pages` folder.

You can set up an **implicit route** by simply adding a file in the folder structure
under the `/content/pages` (by default, configurable in Environment by
`content_path`) or you can make it explicit by adding a route rule to the
Router.

It is also possible to add aliases when the path to the view template file does
not match the desired url. Most obvious example of this is that you probably
will need to alias the root (`example.com/`) path to a file with a name like
`home`. This is done like so:

```php
Router::alias('/', 'home.html');
```

If you want to add an **explicit route** and return something that is not in a
template file, this can be done with the `Router:add` method. It requires
a string to match the url on, a chose of which HTTP method(s) to accept
and a callable (like a closure) to call should the Route be chosen. Any
callable given to the Route MUST return a Response, and will be sent a
Request as the only argument.

Example of explicit Route:
```php
Router::add('version', function(Request $r): Response {
    return new Json(['version' => '1.3']);
});
```

There is also **dynamic routes**, which are like the explicit, except there
are parts of the url that can contain **url parameters**. This will then
be made available to the routed callbable. The string match now is not
a direct string comparison, but a regex. To match and extract the url
parameters the Router uses [preg_match](http://php.net/manual/en/function.preg-match.php),
with the url parameters being named groups.

Example of dynamic Route. We see that the url has to start with `/api/tasks/`
and then followed by digits, at least one. The digits will be extracted and
made available to the
```php
// Get task by id, i.e. GET http://example.com/api/tasks/12
Router::add(
  '|^api/tasks/(?<id>\d+)$|',
  function(Request $request): Response
  {
    $id = $request->param('id'); // from the regex matched url part
    $data_model = app\Task::get($id);
    return new Json($data_model); // since Task model implements \JsonSerializable
  },
  Http::GET
);
```


