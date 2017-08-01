<?php
// ***********
define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT');
define('ROOT', realpath(dirname(dirname(__FILE__))));
define('VENDOR_PATH', ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR);
// ***********
require_once(VENDOR_PATH . 'autoload.php');
require_once(VENDOR_PATH . 'alkemann/h2l/src/bootstrap.php');

// Default error handler
set_exception_handler('alkemann\h2l\handleError');
set_error_handler('alkemann\h2l\handleWarning', E_WARNING);


$request = new alkemann\h2l\Request($_REQUEST, $_SERVER, $_GET, $_POST);
alkemann\h2l\Log::debug("== Request: " . $request->route()->url);
$response = $request->response();
if ($response)
    echo $response->render();
