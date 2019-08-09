#!/bin/bash

WEBPORT=${1:-8088}

export ENV='LOCAL'
open http://localhost:$WEBPORT
php -S localhost:$WEBPORT -t webroot webroot/server.php
