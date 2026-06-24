#!/bin/bash
# Serves the GoLocal portal on localhost:8000. Run/kept-alive by launchd.
cd /Users/tomjeffrey/golocal || exit 1
export PHP_CLI_SERVER_WORKERS=4
exec /usr/local/bin/php artisan serve --host=127.0.0.1 --port=8000
