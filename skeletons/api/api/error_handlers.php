<?php

namespace api;

use alkemann\h2l\Log;
use alkemann\h2l\Environment;
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\response\{Error, Json};
use Error as PhpError;

/**
 * May be set as exception handler, i.e. set_exception_handler('alkemann\h2l\handleError');
 */
function handleError(\Throwable $e): void
{
    if ($e instanceof \Exception) {
        Log::error(get_class($e) . ": " . $e->getMessage());
    } elseif ($e instanceof PhpError) {
        Log::alert(get_class($e) . ": " . $e->getMessage());
    }

    if (Environment::get('debug')) {
        header("HTTP/1.0 500 Internal Server Error");
        header("Content-type: text/html");
        if (isset($e->xdebug_message)) {
            echo '<table>' . $e->xdebug_message . '</table><br>';
        } else {
            echo '<h1 style="color:red;">' . $e->getMessage() . '</h1>';
            echo '<h3>' . $e->getFile() . ' :: ' . $e->getLine() . '</h3>';
            echo '<pre>' . $e->getTraceAsString() . '</pre><br>';
        }
        var_dump($e);  // Change to d() of using alkemann/debug
    } else {
        echo (new Error(['msg' => $e->getMessage()], ['template' => 'error', 'code' => 500]))->render();
    }
}

function handleWarning($errno, $message, $file, $line, $meta): void
{
    if (Environment::get('debug')) {
        header("Content-type: text/html");
        echo '<h1 style="color:red;">' . $message . '</h1>';
        echo '<h3>' . $file . ' :: ' . $line . '</h3>';
        var_dump($meta);  // Change to d() of using alkemann/debug
        die();
    } else {
        error_log("WARNING: {$file}::{$line} : $errno : $message : " . preg_replace("|\s+|", " ",
                print_r($meta, true)));
    }
}
