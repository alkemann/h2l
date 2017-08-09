<?php
// ***********
$ROOT = realpath(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR;
$VENDOR_PATH = $ROOT . 'vendor' . DIRECTORY_SEPARATOR;
// ***********

require_once($VENDOR_PATH . 'autoload.php');

use alkemann\h2l\Environment;

Environment::set([
    Environment::DEV => [
        'debug' => true,
        'content_path' => $ROOT . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
    ],
    Environment::PROD => [
        'debug' => false,
        'content_path' => $ROOT . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
    ]
], Environment::ALL);

alkemann\h2l\Router::alias('/', 'home.html');

$request = new alkemann\h2l\Request($_REQUEST, $_SERVER, $_GET, $_POST);
$response = $request->response();
if ($response) {
    echo $response->render();
}
