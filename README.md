# H2L - microframework

[![Travis](https://img.shields.io/travis/alkemann/h2l.svg)]()
[![Packagist](https://img.shields.io/packagist/l/alkemann/h2l.svg)]()
[![Tag](https://img.shields.io/github/tag/alkemann/h2l.svg)]()
[![PHP](https://img.shields.io/badge/PHP_version-7.1-green.svg)]()

Get started quickly with static pages and small apis

## Requirements

 + PHP 7


## Install

 + Install via composer: `composer require alkemann/h2l`
 + While you can use the library as only a lib, it comes with an index.php file.
   The library comes with 3 skeletons to get you started, the "min" contains basically
   just the index.php, the minimum expected folder structure and a hello world base route.
   You can also use the "base" that contains a an app, some base css, config files.
   Thirdly there is an "example" version that contains some more illustative example pages
   and dynamic routes. install by simply copying the skeleton folder contents of choice down
   to the root of your app (presumably the same folder that contains the "vendor" composer):
   `vendor/bin/h2l skeleton base`


## Usage

 + Change the homepage by changing the file `content/pages/home.html.php`
 + Add files and folders to `content/pages` to add fixed routed content
 + Include a route file in `webroot/index.php` or add to `resources/configs/routes.php` if you installed the base skeleton.
 + Add dynamic routes there by supplying a regex match on url and a closure handler:

Some example routes:
 ```
use alkemann\h2l\{Request, Router, Result};

// Get task by id, i.e. http://example.com/api/tasks/12
Router::add('|^api/tasks/(?<id>\d+)$|', function(Request $request) {
	$id = $request->param('id'); // from the regex matched url part
	$data_model = app\Task::get($id);
	return new Result($data_model); // since Task model implements \JsonSerializable
}, Request::GET);

// Any url with `version` in it, i.e. http://example.com/somethi/versionista
Router::add('|version|' function($r) {
	return new Result(['version' => '1.3']);
});

 ```


## Tests

To run tests you must can checkout the repo and require with dev and run `./bin/runtests` in the same folder as this README.md.

Or to run tests on the vendor included lib into your application, you must also require phpunit; `composer require phpunit/phpunit` and then you can run h2l tests with `vendor/bin/testh2l`
