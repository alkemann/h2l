<?php

namespace alkemann\h2l;

require_once 'internals/functions.php';

function handleError(\Exception $e, Request $request) {
	if ($e instanceof \alkemann\h2l\exceptions\InvalidUrl) {
		return (new Error(404, $e->getMessage()))->render();
	}
	if (DEBUG) {
		echo $e->xdebug_message;
		d($e, $request);
	} else {
		(new Error(500, $e->getMessage()))->render();
	}
}
