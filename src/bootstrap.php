<?php

namespace alkemann\h2l;

if (DEBUG) require_once 'internals/functions.php';

/**
 * May be set as exception handler, i.e. set_exception_handler('alkemann\h2l\handleError');
 *
 * @param \Throwable $e
 */
function handleError(\Throwable $e) : void
{
    if ($e instanceof \alkemann\h2l\exceptions\InvalidUrl) {
        Log::info("InvalidUrl: " . $e->getMessage());
        echo (new response\Error(null, 404, ['message' => $e->getMessage()]))->render();
        return;
    }
    if ($e instanceof \Exception) {
        Log::error(get_class($e) . ": " . $e->getMessage());
    } elseif ($e instanceof \Error) {
        Log::alert(get_class($e) . ": " . $e->getMessage());
    }

    if (DEBUG && isset($e->xdebug_message)) {
        header("Content-type: text/html");
        echo '<table>' . $e->xdebug_message . '</table><br>';
        dbp('xdebug_message');
        d($e);
    } elseif (DEBUG) {
        header("Content-type: text/html");
        echo '<h1 style="color:red;">' . $e->getMessage() . '</h1>';
        echo '<h3>' . $e->getFile() . ' :: ' . $e->getLine() . '</h3>';
        echo '<pre>' . $e->getTraceAsString() . '</pre><br>';
        d($e);
    } else {
        echo (new response\Error(null, 500, ['message' => $e->getMessage()]))->render();
    }
}

/**
 * @param $errno
 * @param $message
 * @param $file
 * @param $line
 * @param $meta
 */
function handleWarning($errno, $message, $file, $line, $meta) : void
{
    if (DEBUG) {
        header("Content-type: text/html");
        echo '<h1 style="color:red;">' . $message . '</h1>';
        echo '<h3>' . $file. ' :: ' . $line . '</h3>';
        d($meta);
        die();
    } else {
        error_log("WARNING: {$file}::{$line} : $errno : $message : " . preg_replace("|\s+|", " ", print_r($meta, true)));
    }
}
