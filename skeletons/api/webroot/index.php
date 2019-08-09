<?php
// ***********
$ROOT = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$VENDOR_PATH = $ROOT . 'vendor' . DIRECTORY_SEPARATOR;
// ***********

require_once($VENDOR_PATH . 'autoload.php');

use alkemann\h2l\{ Environment, Dispatch };

Environment::set([
    Environment::DEV => [
        'debug' => true,
    ],
    Environment::PROD => [
        'debug' => false,
    ]
], Environment::ALL);

require_once $ROOT . 'api/App.php'; /* Replace this with autoloading the api folder with composer:
	"autoload": {
        "psr-4": {
            "api\\": "api/"
        }
    }
*/

// This adds all the routes from the app
api\App::addAppRoutes();

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
$dispatch->registerMiddle(api\App::getMiddlewares()); // Optional if middlewares are created for the App
$response = $dispatch->response();
echo ($response) ? $response->render() : '';
