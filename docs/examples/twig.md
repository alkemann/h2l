# Example: Use Twig as templating

We simply create a Response subclass that injects any variables into the Twig templates, renders it
and sets the rendered output as the `Message` body.

#### backend/response/Twig.php
```php
namespace backend\response;

use alkemann\h2l\{ Environment, Message, Response };
use alkemann\h2l\util\Http;

class Twig extends Response
{

    public function __construct(array $data, int $code = Http::CODE_OK, array $config = [])
    {
        $template = ($config['template'] ?? 'fallback') . '.html';

        $twig = new \Twig\Environment(
            new \Twig\Loader\FilesystemLoader(Environment::get('twig_templates_path')),
            ['cache' => Environment::get('twig_cache_path')]
        );

        $output = $twig->render($template, $data);

        $this->config = $config;
        $this->message = (new Message())
            ->withCode($code)
            ->withHeaders(['Content-Type' => Http::CONTENT_HTML])
            ->withBody($output)
        ;
    }
 
    public function render(): string
    {
        $this->setHeaders();
        return $this->message->body();
    }
}
```

#### backend/App.php
```php
namespace backend;

use alkemann\h2l\{ Request, Response };
use backend\response\Twig;

class App
{
    public static function city(Request $r): Response {
        $data = [
            'name' => $r->param('city'),
            'street' => 'Main str.',
            'cars' => ['Volva', 'Tesla', 'Subaru']
        ];
        return new Twig($data, 200, ['template' => 'city']);
    }
}
```

#### configs/environment.php
```php
use alkemann\h2l\{ Environment, Dispatch, Log };

Environment::set([
    'debug' => true,
    'twig_templates_path' => $ROOT . 'templates' . DIRECTORY_SEPARATOR,
    'twig_cache_path' => $ROOT . 'cache' . DIRECTORY_SEPARATOR,
]);
```


#### configs/routes.php
```php

use alkemann\h2l\{ Router, Request, Response };
use backend\response\Twig;

Router::add(
    '/', // i.e. http://localhost:8088/
    function(Request $r): Response {
        return new Twig(['name' => 'Johnny Cash'], 200, ['template' => 'home']);
    }
);
// i.e. http://localhost:8088/city/Berlin
Router::add('|city/(?<city>\w+)|', 'backend\App::city');
```

#### templates/city.html
```html
<h1>Hello {{ name }}!</h1><html>
    <head>
        <title>{{ name }}</title>
    </head>
    <body>
        <h1>City {{ name }}</h1>
        <sub>{{ "now"|date(null, "Europe/Paris") }}</sub>
        <p>On {{ street }}</p>
        <h3>Cars:</h3>
        <ul>
        	{% for car in cars %}
        		<li>{{ car }}</li>
        	{% endfor %}
        </ul>
    </body>
<html>
```

#### webroot/index.php
```php
// ***********
$ROOT = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$VENDOR_PATH = $ROOT . 'vendor' . DIRECTORY_SEPARATOR;
// ***********

require_once($VENDOR_PATH . 'autoload.php');

require_once($ROOT . 'configs' . DIRECTORY_SEPARATOR . 'environment.php');
require_once($ROOT . 'configs' . DIRECTORY_SEPARATOR . 'routes.php');

use alkemann\h2l\{ Environment, Dispatch };

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
$response = $dispatch->response();
echo ($response) ? $response->render() : '';
