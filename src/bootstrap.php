<?php

namespace alkemann\h2l;

function handleError(\Exception $e, Request $request) {
	echo $e->getMessage();
}
