<h1 align="center">H2L - microframework</h1>
<h3 align="center">Get started quickly with static pages and small apis</h3>

<p align="center">
<a href="https://packagist.org/packages/alkemann/h2l"><img src="https://camo.githubusercontent.com/e25562f743654efa776cf5e337593191361a2786/68747470733a2f2f696d672e736869656c64732e696f2f7061636b61676973742f6c2f616c6b656d616e6e2f68326c2e737667" alt="Packagist" data-canonical-src="https://img.shields.io/packagist/l/alkemann/h2l.svg" style="max-width:100%;"></a>
<a href="https://github.com/alkemann/h2l/releases"><img src="https://camo.githubusercontent.com/8c35ac99b3dab86eed3da6a0c175a1f1fb4e5d31/68747470733a2f2f696d672e736869656c64732e696f2f6769746875622f7461672f616c6b656d616e6e2f68326c2e737667" alt="Tag" data-canonical-src="https://img.shields.io/github/tag/alkemann/h2l.svg" style="max-width:100%;"></a>
<a href="http://php.net/ChangeLog-7.php"><img src="https://img.shields.io/badge/PHP%20version-7.4-brightgreen" alt="PHP" style="max-width:100%;"></a>
<a href="https://codecov.io/gh/alkemann/h2l"><img src="https://camo.githubusercontent.com/84df9c42ce79c1b6ad80c3e6784dd08ecc7d814b/68747470733a2f2f636f6465636f762e696f2f67682f616c6b656d616e6e2f68326c2f6272616e63682f6d61737465722f67726170682f62616467652e737667" alt="codecov" data-canonical-src="https://codecov.io/gh/alkemann/h2l/branch/master/graph/badge.svg" style="max-width:100%;"></a>
<a href="https://styleci.io/repos/54427353"><img src="https://camo.githubusercontent.com/06c5acdd9ceb6df2316e67bec61130f738e462ce/68747470733a2f2f7374796c6563692e696f2f7265706f732f35343432373335332f736869656c643f6272616e63683d6d6173746572267374796c653d666c6174" alt="StyleCI" data-canonical-src="https://styleci.io/repos/54427353/shield?branch=master&amp;style=flat" style="max-width:100%;"></a>
<a href="https://scrutinizer-ci.com/g/alkemann/h2l/?branch=master"><img src="https://camo.githubusercontent.com/885f3cd511f91cff8d6b85f10a211d818054b3ee/68747470733a2f2f7363727574696e697a65722d63692e636f6d2f672f616c6b656d616e6e2f68326c2f6261646765732f7175616c6974792d73636f72652e706e673f623d6d6173746572" alt="Scrutinizer Code Quality" data-canonical-src="https://scrutinizer-ci.com/g/alkemann/h2l/badges/quality-score.png?b=master" style="max-width:100%;"></a>
<a href="https://github.com/phpstan/phpstan"><img src="https://img.shields.io/badge/PHPStan-level%20max-brightgreen.svg" /></a>
</p>

## Documentation

Hosted on Github pages: [https://alkemann.github.io/h2l/](https://alkemann.github.io/h2l/)

## Requirements

 + PHP 8.0

## Install

 + Install via composer: `composer require alkemann/h2l`
 + While you can use the library as only a lib, it comes with an index.php file.
   The library comes with 3 skeletons to get you started, the "min" contains basically
   just the index.php, the minimum expected folder structure and a hello world base route.
   You can also use the "base" that contains a an app, some base css, config files.
   Thirdly there is an "example" version that contains some more illustative example pages
   and dynamic routes. install by simply copying the skeleton folder contents of choice down
   to the root of your app (presumably the same folder that contains the "vendor" composer):
   `vendor/bin/skeleton base` (base automagic website using routes by files and folders).
   There more other skeleton alternatives:
     + `min` (bare bones)
     + `heroku_react` (set up for plug and play heroku app with H2L as backend and an react-redux frontend)
     + `heroku_min` (set up for plug and play heroku app with H2L)
     + `api` (no automagic routing, for pure api apps and specifically routed responses)


## Usage from skeleton

 + Change the homepage by changing the file `content/pages/home.html.php`
 + Add files and folders to `content/pages` to add fixed routed content
 + Include a route file in `webroot/index.php` or add to `resources/configs/routes.php` if you installed the base skeleton.
 + Add dynamic routes there by supplying a regex match on url and a closure handler:

Some example routes:
```php
use alkemann\h2l\{Request, Router, Response, response\Json};

// Get task by id, i.e. GET http://example.com/api/tasks/12
Router::add(
  '|^api/tasks/(?<id>\d+)$|',
  function(Request $request): Response
  {
    $id = $request->param('id'); // from the regex matched url part
    $data_model = app\Task::get($id);
    return new Json($data_model); // since Task model implements \JsonSerializable
  }
);

// http://example.com/version
Router::add('version', function($r) {
	return new Json(['version' => '1.3']);
});
```

## Raw usage

A minimal `webroot\index.php` could look something like this
```php
$root_path = realpath(dirname(dirname(__FILE__)));
require_once($root_path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use alkemann\h2l\{Environment, Dispatch};

Environment::setEnvironment(Environment::PROD);
Environment::set([
    'debug' => false,
    'layout_path'  => $root_path . 'layouts' . DIRECTORY_SEPARATOR,
    'content_path' => $root_path .  'pages' . DIRECTORY_SEPARATOR,
]);

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
$response = $dispatch->response();
if ($response) echo $response->render();
```

## Tests

To run tests you must can checkout the repo and require with dev and run `./bin/runtests` in the same folder as this README.md.

Or to run tests on the vendor included lib into your application, you must also require phpunit; `composer require phpunit/phpunit` and then you can run h2l tests with `vendor/bin/testh2l`
