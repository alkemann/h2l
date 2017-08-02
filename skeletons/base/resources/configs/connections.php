<?php

use alkemann\h2l\Connections;

Connections::add(
    'default',
    function() { /* open connection */ },
    function() { /* close connection */ }
);
