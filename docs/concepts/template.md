# Templating

Not to be confused with the [Page concept)[page.md], templating still uses the Page and Error
classes, but is a way of providing content without routing. This is best illustrated with an
example:

```php
// Somewhere you keep your application routes
Router::add('/status', function(Request $request): Page {
	$status = 'UP';
	$template = 'status_page';
	$template_path = '/var/html/www/templates'; // ofc better set relative to root
	return new Page(compact('status'), compact('template', 'template_path'));
});
```
```php
// /var/html/www/templates/status_page.html.php
<h1>Application state:</h1>
<h2>Status: <?php echo $status; ?> </h2>
```

The above code will look for the file `/var/html/www/templates/status_page.html.php`, inject the
variable `$status` into that and render it (the associative array passed as the first argument to
page will be extracted into the scope of the template, `$this` will be the `alkemann\h2l\Page`
instance).

You may (and probably should) set the `template_path` in the Environment. In any of the example
`index.php`s provided by the skeletons, there is a $ROOT variable to the root of the app you
could use, like so: `Environment::set(['template_path' => $ROOT . 'templates/']);`

