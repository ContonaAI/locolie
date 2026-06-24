#!/usr/bin/env bash
#
# Push local data (businesses, offers, categories + photos) up to the live site.
#
#   ./scripts/sync.sh                # full sync (data + photos)
#   ./scripts/sync.sh --skip-images  # data only, faster
#
# Runs `php artisan sync:push` from the project root, wherever you call it from.

set -euo pipefail

# Resolve the project root (parent of this script's folder) so it works from anywhere.
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

echo "▶ Syncing local data to the live site..."
php artisan sync:push "$@"
echo "✓ Done. Check https://locolie.com"
