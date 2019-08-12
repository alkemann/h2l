<?php
// ***********
$ROOT = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$VENDOR_PATH = $ROOT . 'vendor' . DIRECTORY_SEPARATOR;
// ***********

require_once($VENDOR_PATH . 'autoload.php');

use alkemann\h2l\{ Environment, Dispatch, Log };

Environment::set([
    'debug' => true,
    'template_path' => $ROOT . 'templates' . DIRECTORY_SEPARATOR,
]);

Log::handler('standard', [Log::class, 'std']);


require_once $ROOT . 'api' . DIRECTORY_SEPARATOR .  'error_handlers.php';
set_exception_handler('api\handleError');
set_error_handler('api\handleWarning', E_WARNING|E_USER_WARNING);


require_once $ROOT . 'api' . DIRECTORY_SEPARATOR .  'App.php'; /* Replace this with autoloading the api folder with composer:
	"autoload": {
        "psr-4": {
            "api\\": "api/"
        }
    }
*/

// This adds all the routes from the app and registers middlewares if any
$app = new api\App;

$dispatch = new Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();
$dispatch->registerMiddle(...Environment::middlewares());// Optional if middlewares are created for the App
$response = $dispatch->response();
echo ($response) ? $response->render() : '';
