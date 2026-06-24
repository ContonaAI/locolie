#!/bin/bash
# One command to bring the GoLocal portal online: starts the Laravel server
# and the ngrok tunnel on your reserved static domain. Ctrl+C stops both.
set -e
cd "$(dirname "$0")/.." || exit 1

DOMAIN=$(tr -d '[:space:]' < scripts/ngrok-domain.txt 2>/dev/null)
if [ -z "$DOMAIN" ]; then
  echo "✗ No static domain set. Put your ngrok domain in scripts/ngrok-domain.txt first."
  echo "  Get it from https://dashboard.ngrok.com/domains"
  exit 1
fi

echo "▶ Starting Laravel server on http://127.0.0.1:8000 ..."
PHP_CLI_SERVER_WORKERS=4 /usr/local/bin/php artisan serve --host=127.0.0.1 --port=8000 \
  > /tmp/golocal-serve.log 2>&1 &
SERVE_PID=$!

# Stop the server when this script exits.
trap 'echo; echo "■ Stopping..."; kill $SERVE_PID 2>/dev/null; exit 0' INT TERM

sleep 2
echo "▶ Opening tunnel at https://$DOMAIN ..."
echo "  (leave this window open — closing it takes the portal offline)"
/usr/local/bin/ngrok http 8000 --url="$DOMAIN"
