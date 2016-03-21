<?php
// ************
define('FIRST_APP_CONSTANT', 'FIRST_APP_CONSTANT');
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', realpath(dirname(dirname(__FILE__))));
define('WEBROOT_DIR', basename(dirname(__FILE__)));
define('WWW_ROOT', dirname(__FILE__) . DS);
define('CONTENT_PATH', ROOT . DS . 'content' . DS);
define('LAYOUT_PATH', CONTENT_PATH . 'layout' . DS);
define('VENDOR_PATH', ROOT . DS . 'vendor' . DS);
define('LOGS_PATH', ROOT . DS . 'resources/logs' . DS);
define('LOCALES_PATH', ROOT . DS . 'resources/locales' . DS);
define('CONFIG_PATH', ROOT . DS . 'resources/configs' . DS);
define('H2L_PATH', VENDOR_PATH . '' . DS );

require_once(VENDOR_PATH . 'autoload.php');
require_once(VENDOR_PATH . 'alkemann/h2l/src/bootstrap.php');
// ***********

include_once(CONFIG_PATH . 'defines.php');
include_once(CONFIG_PATH . 'routes.php');

$request = new alkemann\h2l\Request($_REQUEST, $_SERVER, $_GET, $_POST);

try {
    $response = $request->response();
    if ($response)
    	$response->render();
} catch (Exception $e) {
   alkemann\h2l\handleError($e, $request);
}
