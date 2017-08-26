<?php

define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT');

$root_path = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$vendor_path = $root_path . 'vendor' . DIRECTORY_SEPARATOR;
$config_path = $root_path . 'configs' . DIRECTORY_SEPARATOR;

require_once $vendor_path . 'autoload.php';

require_once $config_path . 'error_handlers.php';
set_exception_handler('api\handleError');
set_error_handler('api\handleWarning', E_WARNING);

require_once $config_path . 'environments.php';
require_once $config_path . 'routes.php';

$dispatch = new alkemann\h2l\Dispatch($_REQUEST, $_SERVER, $_GET, $_POST);
$dispatch->setRouteFromRouter();

$response = $dispatch->response();
if ($response) {
    echo $response->render();
}
