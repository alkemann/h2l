<?php
namespace local_server;

if ($_SERVER['REQUEST_URI'] != '/' && file_exists($_SERVER['DOCUMENT_ROOT'] . $_SERVER['REQUEST_URI'])) {
    return false;
}

$_SERVER['PHP_SELF'] = '/index.php';

$REQUEST_URI = $_SERVER['REQUEST_URI'];
if (strpos($REQUEST_URI, '?')) {
    $extract = function(string $REQUEST_URI): array {
        list($REQUEST_URI, $things) = explode('?', $REQUEST_URI, 2);
        $params = explode('&', $things);
        $gets = [];
        foreach ($params as $param) {
            if (strpos($param, '=')) {
                list($key, $value) = explode('=', $param, 2);
            } else {
                $key = $param;
                $value = '';
            }
            $gets[$key] = $value;
        }
        return [$REQUEST_URI, $gets];
    };
    list($REQUEST_URI, $gets) = $extract($REQUEST_URI);
    $_GET = $gets;
}

$_GET['url'] = $_REQUEST['url'] = $REQUEST_URI;
unset($REQUEST_URI);

require 'index.php';
