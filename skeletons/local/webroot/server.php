<?php
namespace local_server;

function put_url_in_request()
{
    $req = $_SERVER['REQUEST_URI'];
    if ($req != '/' && file_exists($_SERVER['DOCUMENT_ROOT'] . $req)) {
        return false;
    }

    $_SERVER['PHP_SELF'] = '/index.php';

    $url = $_SERVER['REQUEST_URI'];
    if (strpos($url, '?')) {
        $extract = function(string $url): array {
            list($url, $things) = explode('?', $url, 2);
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
            return [$url, $gets];
        };
        list($url, $gets) = $extract($url);
        $_GET = $gets;
    }

    $_GET['url'] = $_REQUEST['url'] = $url;
}
put_url_in_request();
require 'index.php';
