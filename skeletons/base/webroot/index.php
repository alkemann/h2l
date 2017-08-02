<?php
define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT'); // For debug purposes
// ************
$DS = DIRECTORY_SEPARATOR;
$ROOT = realpath(dirname(dirname(__FILE__)));
$VENDOR_PATH = $ROOT . $DS . 'vendor' . $DS;
$CONFIG_PATH = $ROOT . $DS . 'resources' . $DS . 'configs' . $DS;
require_once($VENDOR_PATH . 'autoload.php');

// Default error handling
require_once($VENDOR_PATH . 'alkemann' . $DS . 'h2l' . $DS . 'src' . $DS . 'bootstrap.php');
set_exception_handler('alkemann\h2l\handleError');
set_error_handler('alkemann\h2l\handleWarning', E_WARNING);

// Include configuration files
require_once($CONFIG_PATH . 'environments.php');
require_once($CONFIG_PATH . 'connections.php');
require_once($CONFIG_PATH . 'routes.php');
// ***********

$request = new alkemann\h2l\Request($_REQUEST, $_SERVER, $_GET, $_POST);
alkemann\h2l\Log::debug("== Request: " . $request->route()->url);
$response = $request->response();
if ($response) {
    echo $response->render();
}
