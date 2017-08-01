<?php

use alkemann\h2l\Environment;
use alkemann\h2l\Log;

$base = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR ;

Environment::set([
    Environment::DEV => [
        'debug' => true,
        'content_path' => $base . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'layouts' . DIRECTORY_SEPARATOR
    ],
    Environment::PROD => [
        'debug' => false,
        'content_path' => $base . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'layouts' . DIRECTORY_SEPARATOR
    ]
);


// Check for server host and set enviroment here for example

alkemann\h2l\Log::handler('file', ['alkemann\h2l\Log', 'file']);
