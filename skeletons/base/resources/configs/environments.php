<?php

use alkemann\h2l\Environment;
use alkemann\h2l\Log;

$base = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR ;

Environment::set([
    Environment::DEV => [
        'debug' => true,
        'logs_path' => $base . 'resources' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        'content_path' => $base . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'layouts' . DIRECTORY_SEPARATOR
    ],
    Environment::PROD => [
        'debug' => false,
        'logs_path' => $base . 'resources' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        'content_path' => $base . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'layouts' . DIRECTORY_SEPARATOR
    ]
], Environment::ALL);


// Check for server host and set environment here for example


Log::handler('file', [Log::class, 'file']);
