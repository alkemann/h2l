<?php

namespace app;

use alkemann\h2l\Log;
use alkemann\h2l\Environment;
use alkemann\h2l\exceptions\InvalidUrl;
use alkemann\h2l\response\Error;
use Error as PhpError;

/**
 * May be set as exception handler, i.e. set_exception_handler('alkemann\h2l\handleError');
 *
 * @param \Throwable $e
 */
function handleError(\Throwable $e): void
{
    if ($e instanceof InvalidUrl) {
        Log::info("InvalidUrl: " . $e->getMessage());
        echo (new Error(['message' => $e->getMessage()], ['code' => 404]))->render();
        return;
    }
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
        var_dump($e);
    } else {
        echo (new Error(['message' => $e->getMessage()], ['code' => 500]))->render();
    }
}

/**
 * @param $errno
 * @param $message
 * @param $file
 * @param $line
 * @param $meta
 */
function handleWarning($errno, $message, $file, $line): void
{
    if (Environment::get('debug')) {
        header("Content-type: text/html");
        echo '<h1 style="color:red;">' . $message . '</h1>';
        echo '<h3>' . $file . ' :: ' . $line . '</h3>';
        die();
    } else {
        error_log("WARNING: {$file}::{$line} : $errno : $message : " . preg_replace("|\s+|", " ",
                print_r($meta, true)));
    }
}
