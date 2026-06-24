#!/bin/bash
# Opens the ngrok tunnel to localhost:8000 using your reserved static domain.
# Run/kept-alive by launchd. The domain is read from scripts/ngrok-domain.txt.
cd /Users/tomjeffrey/golocal || exit 1

DOMAIN=$(tr -d '[:space:]' < scripts/ngrok-domain.txt 2>/dev/null)

if [ -z "$DOMAIN" ]; then
  echo "No static domain set in scripts/ngrok-domain.txt — refusing to start." >&2
  exit 1
fi

exec /usr/local/bin/ngrok http 8000 --url="$DOMAIN" --log=stdout
