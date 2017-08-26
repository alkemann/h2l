#!/bin/bash

# Change to correct values
USER=root
PASS=root
HOST=localhost
DB=tests

# Can override the port through `bin/local.sh 8088`
WEBPORT=${1:-8080}

export CLEARDB_DATABASE_URL="mysql://${USER}:${PASS}@${HOST}/${DB}?reconnect=true"
export ENV='LOCAL'
open http://localhost:$WEBPORT
php -S localhost:$WEBPORT -t webroot webroot/server.php
