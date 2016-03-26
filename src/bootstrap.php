<?php

namespace alkemann\h2l;

require_once 'internals/functions.php';

function handleError(\Throwable $e) {
    if ($e instanceof \alkemann\h2l\exceptions\InvalidUrl) {
        echo (new Error(404, $e->getMessage()))->render();
        return;
    }

    if (DEBUG && isset($e->xdebug_message)) {
        header("Content-type: text/html");
        echo '<table>' . $e->xdebug_message . '</table><br>';
        dbp('xdebug_message');
        d($e);
    } elseif (DEBUG) {
        header("Content-type: text/html");
        echo '<h1>' . $e->getMessage() . '</h1>';
        d($e);
    } else {
        (new Error(500, $e->getMessage()))->render();
    }
}
