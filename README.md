# H2L - microframework


[![Packagist](https://img.shields.io/packagist/l/alkemann/h2l.svg)](https://packagist.org/packages/alkemann/h2l)
[![Tag](https://img.shields.io/github/tag/alkemann/h2l.svg)](https://github.com/alkemann/h2l/releases)
[![PHP](https://img.shields.io/badge/PHP_version-7.1-green.svg)](http://php.net/ChangeLog-7.php)

[![Travis](https://img.shields.io/travis/alkemann/h2l.svg)](https://travis-ci.org/alkemann/h2l)
[![codecov](https://codecov.io/gh/alkemann/h2l/branch/master/graph/badge.svg)](https://codecov.io/gh/alkemann/h2l)
[![StyleCI](https://styleci.io/repos/54427353/shield?branch=master&style=flat)](https://styleci.io/repos/54427353)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/alkemann/h2l/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/alkemann/h2l/?branch=master)

Get started quickly with static pages and small apis

## Requirements

 + PHP 7.1

## Documentation

Hosted on Github pages: [https://alkemann.github.io/h2l/](https://alkemann.github.io/h2l/)

## Install

 + Install via composer: `composer require alkemann/h2l`
 + While you can use the library as only a lib, it comes with an index.php file.
   The library comes with 3 skeletons to get you started, the "min" contains basically
   just the index.php, the minimum expected folder structure and a hello world base route.
   You can also use the "base" that contains a an app, some base css, config files.
   Thirdly there is an "example" version that contains some more illustative example pages
   and dynamic routes. install by simply copying the skeleton folder contents of choice down
   to the root of your app (presumably the same folder that contains the "vendor" composer):
   `vendor/bin/skeleton base` (there more other skeleton alternatives, like `min` (bare bones)
   or `heroku` (set up for plug and play heroku app with H2L as backend and an react-redux frontend)).


## Usage from skeleton

 + Change the homepage by changing the file `content/pages/home.html.php`
 + Add files and folders to `content/pages` to add fixed routed content
 + Include a route file in `webroot/index.php` or add to `resources/configs/routes.php` if you installed the base skeleton.
 + Add dynamic routes there by supplying a regex match on url and a closure handler:

Some example routes:
```php
use alkemann\h2l\{Request, Router, response\Json};

// Get task by id, i.e. GET http://example.com/api/tasks/12
Router::add(
  '|^api/tasks/(?<id>\d+)$|',
  function(Request $request): Response
  {
    $id = $request->param('id'); // from the regex matched url part
    $data_model = app\Task::get($id);
    return new Json($data_model); // since Task model implements \JsonSerializable
  },
  Request::GET
);

// Any url with `version` in it, i.e. http://example.com/somethi/versionista
Router::add('|version|', function($r) {
	return new Json(['version' => '1.3']);
});
```

## Raw usage

A minimal `webroot\index.php` could look something like this
```php
$root_path = realpath(dirname(dirname(__FILE__)));
require_once($root_path . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use alkemann\h2l\{Environment, Dispatch};

Environment::set([
    'debug' => false,
    'logs_path' => $root_path . 'logs' . DIRECTORY_SEPARATOR,
    'layout_path'  => $root_path . 'layouts' . DIRECTORY_SEPARATOR,
    'content_path' => $root_path .  'pages' . DIRECTORY_SEPARATOR,
], Environment::PROD);
Environment::setEnvironment(Environment::PROD);

$response = (new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST))->response();
if ($response) echo $response->render();
```

## Tests

To run tests you must can checkout the repo and require with dev and run `./bin/runtests` in the same folder as this README.md.

Or to run tests on the vendor included lib into your application, you must also require phpunit; `composer require phpunit/phpunit` and then you can run h2l tests with `vendor/bin/testh2l`
