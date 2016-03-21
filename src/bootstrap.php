<?php

namespace alkemann\h2l;

require_once 'internals/functions.php';

function handleError(\Exception $e, Request $request) {
	echo $e->getMessage();
}
