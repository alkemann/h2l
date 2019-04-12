<?php

use alkemann\h2l\Environment;
use alkemann\h2l\Log;

$base = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR ;

Environment::set([
    Environment::DEV => [
        'debug' => true,
        'logs_path' => $base . 'resources' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        'content_path' => $base . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'content' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR
    ],
    Environment::PROD => [
        'debug' => false,
        'logs_path' => $base . 'resources' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR,
        'content_path' => $base . 'content' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR,
        'layout_path'  => $base . 'content' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR
    ]
], Environment::ALL);

Log::handler('file', [Log::class, 'file']);

// Middleware to add a log response for request and what response handler is chosen
Environment::addMiddle(
    function(Request $request, Chain $chain): ?Response {
        Log::debug("== REQUEST: {$request->method()} '{$request->url()}' ==");
        $response = $chain->next($request);
        if ($response) Log::debug("== Response Handler: " . get_class($response));
        else Log::debug("== Null Response");
        return $response;
    },
    Environment::ALL
);

// Check for server host and set environment here for example
